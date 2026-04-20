<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderReceiptNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LandingReservationController extends Controller
{
    public function __construct(
        private readonly OrderReceiptNotifier $orderReceiptNotifier,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
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
                'pickup_date' => null,
                'pickup_time' => null,
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

    public function showReceipt(Request $request, Order $order)
    {
        $metadata = json_decode((string) ($order->notes ?? ''), true);
        $receiptMeta = is_array($metadata) ? $metadata : [];

        $requestedToken = (string) $request->query('token', '');
        $storedToken = (string) ($receiptMeta['receipt_token'] ?? '');
        $tokenMatched = $requestedToken !== '' && hash_equals($storedToken, $requestedToken);

        if (!$tokenMatched) {
            if (!Auth::check() || !$this->isReceiptViewAllowedForAuthenticatedUser($order, $request->user()->id)) {
                abort(Response::HTTP_FORBIDDEN);
            }
        }

        $order->loadMissing([
            'user:id,name',
            'product:id,name,unit,price_per_unit,establishment_id',
            'product.establishment:id,name,address',
        ]);

        return view('reservations.official-receipt', [
            'order' => $order,
            'receiptMeta' => $receiptMeta,
            'reservationCode' => 'BRH-ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
        ]);
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

    private function resolveOrderingUser(Request $request, array $validated): User
    {
        if ($request->user()) {
            return $request->user();
        }

        $phone = (string) $validated['phone'];
        $email = (string) $validated['email'];
        $fullName = (string) $validated['full_name'];

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
            'email_verified_at' => now(),
        ]);
    }
}
