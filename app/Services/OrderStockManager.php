<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ResellerProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStockManager
{
    public function applyStatusTransition(Order $order, string $nextStatus): Order
    {
        $normalizedNext = $this->normalizeStatus($nextStatus);

        return DB::transaction(function () use ($order, $normalizedNext) {
            /** @var Order|null $lockedOrder */
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->find($order->id);

            if (!$lockedOrder) {
                throw ValidationException::withMessages([
                    'status' => 'Order could not be found.',
                ]);
            }

            $currentStatus = $this->normalizeStatus((string) ($lockedOrder->status ?? 'pending'));

            // A completed order must never be cancelled by the scheduled pickup expiry job
            // or by a stale status form submission.
            if ($currentStatus === 'completed' && $normalizedNext === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Completed orders cannot be cancelled.',
                ]);
            }

            $stockConsumingStatuses = ['confirmed', 'ready_for_pickup', 'shipped', 'completed'];
            $shouldReserveStock = in_array($normalizedNext, $stockConsumingStatuses, true);
            // Keep stock reserved throughout the fulfilment lifecycle. If an order is
            // cancelled or moved back to pending, restore any previously reserved quantity.
            $wasStockConsuming = in_array($currentStatus, $stockConsumingStatuses, true);
            $shouldReleaseStock = $normalizedNext === 'cancelled'
                || ($normalizedNext === 'pending' && $wasStockConsuming);

            $lockedOrder->loadMissing('product:id,stock_quantity');
            $productId = (int) ($lockedOrder->product?->id ?? 0);

            if ($productId <= 0) {
                throw ValidationException::withMessages([
                    'status' => 'Order product is missing.',
                ]);
            }

            /** @var Product|null $lockedProduct */
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->find($productId);

            if (!$lockedProduct) {
                throw ValidationException::withMessages([
                    'status' => 'Order product is unavailable.',
                ]);
            }

            $quantity = max(0, (int) ($lockedOrder->quantity ?? 0));
            $isStockReserved = (bool) ($lockedOrder->stock_reserved ?? false);

            if ($shouldReserveStock && !$isStockReserved) {
                $availableStock = max(0, (int) ($lockedProduct->stock_quantity ?? 0));
                if ($quantity > $availableStock) {
                    throw ValidationException::withMessages([
                        'status' => 'Not enough stock available to confirm this order.',
                    ]);
                }

                $lockedProduct->stock_quantity = $availableStock - $quantity;
                $lockedProduct->save();
                $this->syncResellerListingStock($lockedProduct);

                $lockedOrder->stock_reserved = true;
            }

            if ($shouldReleaseStock && $isStockReserved && $currentStatus !== 'cancelled') {
                $currentStock = max(0, (int) ($lockedProduct->stock_quantity ?? 0));
                $lockedProduct->stock_quantity = $currentStock + $quantity;
                $lockedProduct->save();
                $this->syncResellerListingStock($lockedProduct);

                $lockedOrder->stock_reserved = false;
            }

            $lockedOrder->status = $normalizedNext;
            $lockedOrder->save();

            return $lockedOrder;
        });
    }

    private function syncResellerListingStock(Product $product): void
    {
        if ((string) ($product->seller_type ?? '') !== 'reseller' || empty($product->seller_id)) {
            return;
        }

        ResellerProduct::query()
            ->where('product_id', $product->id)
            ->where('reseller_id', $product->seller_id)
            ->update(['stock_quantity' => max(0, (int) $product->stock_quantity)]);
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        return $normalized === 'canceled' ? 'cancelled' : $normalized;
    }
}
