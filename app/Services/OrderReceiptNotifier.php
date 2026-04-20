<?php

namespace App\Services;

use App\Mail\OrderReceiptStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderReceiptNotifier
{
    public function sendOrderCreated(Order $order): void
    {
        $this->send($order, 'created');
    }

    public function sendStatusUpdated(Order $order): void
    {
        $this->send($order, 'status_updated');
    }

    private function send(Order $order, string $eventType): void
    {
        $order->load([
            'user:id,name,email',
            'product:id,name,unit,price_per_unit,seller_id,seller_type,establishment_id',
            'product.establishment',
        ]);

        $metadata = json_decode((string) ($order->notes ?? ''), true);
        $receiptMeta = is_array($metadata) ? $metadata : [];

        if ((string) ($order->product?->name ?? '') === '' && (int) ($order->product_id ?? 0) > 0) {
            $productName = (string) (DB::table('products')
                ->where('id', (int) $order->product_id)
                ->value('name') ?? '');

            if ($productName !== '') {
                $receiptMeta['product_name'] = $productName;
            }
        }

        $recipientEmail = (string) ($receiptMeta['receipt_email'] ?? $order->user?->email ?? '');
        if ($recipientEmail === '') {
            return;
        }

        $receiptToken = (string) ($receiptMeta['receipt_token'] ?? '');
        $receiptUrlParams = ['order' => $order->id];
        if ($receiptToken !== '') {
            $receiptUrlParams['token'] = $receiptToken;
        }

        $receiptUrl = route('reservations.orders.receipt', $receiptUrlParams);

        Mail::to($recipientEmail)->send(new OrderReceiptStatusMail(
            order: $order,
            receiptMeta: $receiptMeta,
            reservationCode: 'BRH-ORDER-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            receiptUrl: $receiptUrl,
            eventType: $eventType,
        ));
    }
}
