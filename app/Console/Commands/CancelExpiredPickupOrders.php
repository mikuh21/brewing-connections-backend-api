<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderReceiptNotifier;
use App\Services\OrderStockManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CancelExpiredPickupOrders extends Command
{
    protected $signature = 'orders:cancel-expired-pickups';

    protected $description = 'Automatically cancel pickup orders that were not completed within three days of their scheduled pickup time.';

    public function handle(OrderStockManager $stockManager, OrderReceiptNotifier $notifier): int
    {
        $cancelledCount = 0;

        Order::query()
            ->whereRaw('LOWER(COALESCE(payment_mode, \'pickup\')) = ?', ['pickup'])
            ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
            ->whereNotNull('pickup_date')
            ->whereNotNull('pickup_time')
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($stockManager, $notifier, &$cancelledCount) {
                foreach ($orders as $order) {
                    try {
                        $pickupDate = $order->pickup_date instanceof Carbon
                            ? $order->pickup_date->format('Y-m-d')
                            : (string) $order->pickup_date;
                        $pickupAt = Carbon::parse($pickupDate . ' ' . (string) $order->pickup_time);
                        $deadline = $pickupAt->copy()->addDays(3);

                        if (now()->lt($deadline)) {
                            continue;
                        }

                        // Refresh before transition so a recently completed/cancelled order is not expired.
                        $order->refresh();
                        $currentStatus = strtolower((string) ($order->status ?? 'pending'));
                        $currentMode = strtolower((string) ($order->payment_mode ?? 'pickup'));

                        if ($currentMode !== 'pickup'
                            || in_array($currentStatus, ['completed', 'cancelled', 'canceled'], true)) {
                            continue;
                        }

                        $order = $stockManager->applyStatusTransition($order, 'cancelled');
                        $cancelledCount++;

                        try {
                            $notifier->sendStatusUpdated($order);
                        } catch (Throwable $mailException) {
                            Log::warning('Expired pickup order was cancelled, but its email notification failed.', [
                                'order_id' => $order->id,
                                'error' => $mailException->getMessage(),
                            ]);
                        }
                    } catch (Throwable $exception) {
                        Log::warning('Could not automatically cancel an expired pickup order.', [
                            'order_id' => $order->id ?? null,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Automatically cancelled {$cancelledCount} expired pickup order(s).");

        return self::SUCCESS;
    }
}
