<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use App\Services\OrderReceiptNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LandingReservationController extends Controller
{
    private const PRODUCTION_LANDING_BASE_URL = 'https://brewing-hub.online';

    public function __construct(
        private readonly OrderReceiptNotifier $orderReceiptNotifier,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'prefill_token' => ['nullable', 'string'],
            'pickup_date' => ['nullable', 'date_format:Y-m-d'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $orderingUser = $this->resolveOrderingUser($request, $validated);

            /** @var Product|null $product */
            $product = Product::query()
                ->lockForUpdate()
                ->find($validated['product_id']);

            if (!$product) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected product does not exist.');
            }

            if ($product->is_active === false) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'This product is currently unavailable.');
            }

            $requestedQty = (int) $validated['quantity'];
            $minimumQty = max(1, (int) ($product->moq ?? 1));

            if ($requestedQty < $minimumQty) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Quantity is below minimum order quantity.');
            }

            $availableStock = max(0, (int) ($product->stock_quantity ?? 0));
            if ($requestedQty > $availableStock) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Not enough stock available for this order.');
            }

            $totalPrice = round(((float) ($product->price_per_unit ?? 0)) * $requestedQty, 2);
            $receiptToken = Str::random(40);
            $metadata = [
                'source' => 'landing-web',
                'is_guest_checkout' => !Auth::check(),
                'full_name' => $validated['full_name'],
                'receipt_email' => $validated['email'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'pickup_date' => $validated['pickup_date'] ?? null,
                'pickup_time' => $validated['pickup_time'] ?? null,
                'receipt_token' => $receiptToken,
            ];

            $order = Order::query()->create([
                'user_id' => (int) $orderingUser->id,
                'product_id' => (int) $product->id,
                'quantity' => $requestedQty,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'stock_reserved' => false,
                'notes' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'pickup_date' => $validated['pickup_date'] ?? null,
                'pickup_time' => $validated['pickup_time'] ?? null,
            ]);

            $order->setAttribute('receipt_token', $receiptToken);

            return $order;
        });

        $receiptToken = (string) ($order->getAttribute('receipt_token') ?? '');

        $this->orderReceiptNotifier->sendOrderCreated($order);

        return response()->json([
            'message' => 'Reservation submitted successfully.',
            'order_id' => (int) $order->id,
            'reservation_id' => 'BRH-ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'receipt_url' => route('reservations.orders.receipt', ['order' => $order->id, 'token' => $receiptToken]),
        ], Response::HTTP_CREATED);
    }

    public function createPrefillToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'pickup_date' => ['nullable', 'date_format:Y-m-d'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
        ]);

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $request->user();
        if (!$authenticatedUser) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $payload = [
            'authenticated_user_id' => (int) $authenticatedUser->id,
            'full_name' => (string) ($authenticatedUser->name ?? ''),
            'email' => (string) ($authenticatedUser->email ?? ''),
            'product_id' => isset($validated['product_id']) ? (int) $validated['product_id'] : null,
            'quantity' => isset($validated['quantity']) ? (int) $validated['quantity'] : null,
            'pickup_date' => isset($validated['pickup_date']) ? (string) $validated['pickup_date'] : null,
            'pickup_time' => isset($validated['pickup_time']) ? (string) $validated['pickup_time'] : null,
        ];

        $prefillToken = $this->encodePrefillPayload($payload, now()->addMinutes(20)->timestamp);

        $landingParams = array_filter([
            'prefill_token' => $prefillToken,
            'product_id' => $payload['product_id'],
            'quantity' => $payload['quantity'],
            'pickup_date' => $payload['pickup_date'],
            'pickup_time' => $payload['pickup_time'],
        ], static fn ($value) => $value !== null && $value !== '');

        return response()->json([
            'prefill_token' => $prefillToken,
            'expires_in_seconds' => 1200,
            'landing_url' => $this->buildLandingUrl($landingParams),
        ]);
    }

    public function getPrefillData(string $token): JsonResponse
    {
        $payload = $this->decodePrefillPayload($token);

        if (!is_array($payload)) {
            $payload = Cache::get($this->prefillCacheKey($token));
        }

        if (!is_array($payload)) {
            return response()->json([
                'message' => 'Prefill token is invalid or expired.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'full_name' => (string) ($payload['full_name'] ?? ''),
            'email' => (string) ($payload['email'] ?? ''),
            'product_id' => isset($payload['product_id']) ? (int) $payload['product_id'] : null,
            'quantity' => isset($payload['quantity']) ? (int) $payload['quantity'] : null,
            'pickup_date' => isset($payload['pickup_date']) ? (string) $payload['pickup_date'] : null,
            'pickup_time' => isset($payload['pickup_time']) ? (string) $payload['pickup_time'] : null,
        ]);
    }

    public function showReceipt(Request $request, Order $order)
    {
        $receiptMeta = $this->resolveReceiptMeta($order);
        $this->authorizeReceiptAccess($request, $order, $receiptMeta);

        $order->loadMissing([
            'user:id,name',
            'product:id,name,unit,price_per_unit,establishment_id',
            'product.establishment:id,name,address',
            'productRating:id,order_id,created_at,overall_rating,image',
        ]);

        return view('reservations.official-receipt', [
            'order' => $order,
            'receiptMeta' => $receiptMeta,
            'reservationCode' => 'BRH-ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'productRatingUrl' => route('reservations.orders.rating.form', $this->buildReceiptRouteParams($order, $receiptMeta)),
        ]);
    }

    public function showProductRatingForm(Request $request, Order $order)
    {
        $receiptMeta = $this->resolveReceiptMeta($order);
        $this->authorizeReceiptAccess($request, $order, $receiptMeta);

        $order->loadMissing([
            'user:id,name,email',
            'product:id,name,unit,price_per_unit,image_url,establishment_id',
            'product.establishment:id,name,address',
            'productRating:id,order_id,overall_rating,created_at,image',
        ]);

        abort_if((int) ($order->product?->id ?? 0) <= 0, Response::HTTP_NOT_FOUND);

        if (strtolower((string) ($order->status ?? 'pending')) !== 'completed' && ! $order->productRating) {
            return redirect()
                ->route('reservations.orders.receipt', $this->buildReceiptRouteParams($order, $receiptMeta))
                ->with('status', 'Product ratings open once the order is marked completed.');
        }

        return view('reservations.product-rating', [
            'order' => $order,
            'receiptMeta' => $receiptMeta,
            'reservationCode' => 'BRH-ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'receiptUrl' => route('reservations.orders.receipt', $this->buildReceiptRouteParams($order, $receiptMeta)),
            'formAction' => route('reservations.orders.rating.store', $this->buildReceiptRouteParams($order, $receiptMeta)),
        ]);
    }

    public function storeProductRating(Request $request, Order $order)
    {
        $receiptMeta = $this->resolveReceiptMeta($order);
        $this->authorizeReceiptAccess($request, $order, $receiptMeta);

        $order->loadMissing([
            'product:id,name,establishment_id',
            'productRating:id,order_id',
        ]);

        abort_if((int) ($order->product?->id ?? 0) <= 0, Response::HTTP_NOT_FOUND);

        if (strtolower((string) ($order->status ?? 'pending')) !== 'completed') {
            return redirect()
                ->route('reservations.orders.rating.form', $this->buildReceiptRouteParams($order, $receiptMeta))
            ->withErrors(['overall_rating' => 'Only completed orders can be rated.']);
        }

        if ($order->productRating) {
            return redirect()
                ->route('reservations.orders.rating.form', $this->buildReceiptRouteParams($order, $receiptMeta))
                ->with('status', 'You already rated this product.');
        }

        $validated = $request->validate([
            'overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('photo')) {
            $imagePath = $request->file('photo')->store('ratings', 'public');
        }

        $score = (int) $validated['overall_rating'];

        Rating::query()->create([
            'user_id' => (int) $order->user_id,
            'establishment_id' => null,
            'product_id' => (int) $order->product_id,
            'order_id' => (int) $order->id,
            'taste_rating' => $score,
            'environment_rating' => $score,
            'cleanliness_rating' => $score,
            'service_rating' => $score,
            'image' => $imagePath,
        ]);

        return redirect()
            ->route('reservations.orders.rating.form', $this->buildReceiptRouteParams($order, $receiptMeta))
            ->with('status', 'Product rating submitted successfully.');
    }

    private function isReceiptViewAllowedForAuthenticatedUser(Order $order, int $authenticatedUserId): bool
    {
        if ((int) $order->user_id === (int) $authenticatedUserId) {
            return true;
        }

        $order->loadMissing([
            'product:id,seller_id,establishment_id',
            'product.establishment',
        ]);

        if ((int) ($order->product?->seller_id ?? 0) === (int) $authenticatedUserId) {
            return true;
        }

        if ((int) ($order->product?->establishment?->owner_id ?? 0) === (int) $authenticatedUserId) {
            return true;
        }

        return (int) ($order->product?->establishment?->user_id ?? 0) === (int) $authenticatedUserId;
    }

    private function resolveReceiptMeta(Order $order): array
    {
        $metadata = json_decode((string) ($order->notes ?? ''), true);

        return is_array($metadata) ? $metadata : [];
    }

    private function authorizeReceiptAccess(Request $request, Order $order, array $receiptMeta): void
    {
        $requestedToken = (string) $request->query('token', '');
        $storedToken = (string) ($receiptMeta['receipt_token'] ?? '');
        $tokenMatched = $requestedToken !== '' && hash_equals($storedToken, $requestedToken);

        if ($tokenMatched) {
            return;
        }

        if (!Auth::check() || !$this->isReceiptViewAllowedForAuthenticatedUser($order, $request->user()->id)) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    private function buildReceiptRouteParams(Order $order, array $receiptMeta): array
    {
        $params = ['order' => $order->id];
        $receiptToken = (string) ($receiptMeta['receipt_token'] ?? '');

        if ($receiptToken !== '') {
            $params['token'] = $receiptToken;
        }

        return $params;
    }

    private function resolveOrderingUser(Request $request, array $validated): User
    {
        if ($request->user()) {
            return $request->user();
        }

        $prefillToken = (string) ($validated['prefill_token'] ?? '');
        if ($prefillToken !== '') {
            $prefillPayload = $this->decodePrefillPayload($prefillToken);
            $authenticatedUserId = (int) ($prefillPayload['authenticated_user_id'] ?? 0);

            if ($authenticatedUserId > 0) {
                $authenticatedUser = User::query()->find($authenticatedUserId);

                if ($authenticatedUser) {
                    $authenticatedUser->name = (string) $validated['full_name'];

                    if ((string) $authenticatedUser->email !== (string) $validated['email']) {
                        $emailInUse = User::query()
                            ->where('email', (string) $validated['email'])
                            ->where('id', '!=', $authenticatedUser->id)
                            ->exists();

                        if (!$emailInUse) {
                            $authenticatedUser->email = (string) $validated['email'];
                        }
                    }

                    $authenticatedUser->contact_number = (string) $validated['phone'];
                    $authenticatedUser->address = (string) $validated['address'];
                    $authenticatedUser->save();

                    return $authenticatedUser;
                }
            }
        }

        $phone = (string) $validated['phone'];
        $email = (string) $validated['email'];
        $fullName = (string) $validated['full_name'];
        $address = (string) $validated['address'];

        $existingGuest = User::query()
            ->where('role', 'consumer')
            ->where('contact_number', $phone)
            ->first();

        if ($existingGuest) {
            if ((string) $existingGuest->name !== $fullName) {
                $existingGuest->name = $fullName;
            }

            if ((string) $existingGuest->email !== $email) {
                $emailInUse = User::query()
                    ->where('email', $email)
                    ->where('id', '!=', $existingGuest->id)
                    ->exists();

                if (!$emailInUse) {
                    $existingGuest->email = $email;
                }
            }

            $existingGuest->contact_number = $phone;
            $existingGuest->address = $address;
            $existingGuest->save();

            return $existingGuest;
        }

        $emailInUse = User::query()->where('email', $email)->exists();
        $newUserEmail = $emailInUse
            ? ('guest+' . (preg_replace('/\D+/', '', $phone) ?: Str::random(8)) . '.' . now()->timestamp . '@brewhub.local')
            : $email;

        return User::query()->create([
            'name' => $fullName,
            'email' => $newUserEmail,
            'password' => Hash::make(Str::random(32)),
            'role' => 'consumer',
            'status' => 'active',
            'contact_number' => $phone,
            'address' => $address,
            'email_verified_at' => now(),
        ]);
    }

    private function prefillCacheKey(string $token): string
    {
        return 'landing_reservation_prefill:' . $token;
    }

    private function buildLandingUrl(array $landingParams): string
    {
        $baseUrl = $this->resolveLandingBaseUrl();
        $query = http_build_query($landingParams);

        return $baseUrl . '/?' . $query . '#farm-products';
    }

    private function resolveLandingBaseUrl(): string
    {
        $configuredUrl = rtrim((string) config('app.url', self::PRODUCTION_LANDING_BASE_URL), '/');

        if ($configuredUrl === '') {
            return self::PRODUCTION_LANDING_BASE_URL;
        }

        $host = strtolower((string) parse_url($configuredUrl, PHP_URL_HOST));

        $isLocalHost =
            $host === '' ||
            $host === 'localhost' ||
            $host === '127.0.0.1' ||
            $host === '0.0.0.0' ||
            str_ends_with($host, '.localhost') ||
            preg_match('/^192\.168\./', $host) === 1 ||
            preg_match('/^10\./', $host) === 1 ||
            preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $host) === 1;

        return $isLocalHost ? self::PRODUCTION_LANDING_BASE_URL : $configuredUrl;
    }

    private function encodePrefillPayload(array $payload, int $expiresAtTimestamp): string
    {
        $serializedPayload = json_encode([
            'exp' => $expiresAtTimestamp,
            'data' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $encryptedPayload = Crypt::encryptString($serializedPayload);

        return rtrim(strtr(base64_encode($encryptedPayload), '+/', '-_'), '=');
    }

    private function decodePrefillPayload(string $token): ?array
    {
        try {
            $encryptedPayload = base64_decode(strtr($token, '-_', '+/'), true);

            if ($encryptedPayload === false || $encryptedPayload === '') {
                return null;
            }

            $serializedPayload = Crypt::decryptString($encryptedPayload);
            $decodedPayload = json_decode($serializedPayload, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decodedPayload)) {
                return null;
            }

            $expiresAtTimestamp = (int) ($decodedPayload['exp'] ?? 0);
            if ($expiresAtTimestamp <= now()->timestamp) {
                return null;
            }

            $payload = $decodedPayload['data'] ?? null;

            return is_array($payload) ? $payload : null;
        } catch (Throwable) {
            return null;
        }
    }
}
