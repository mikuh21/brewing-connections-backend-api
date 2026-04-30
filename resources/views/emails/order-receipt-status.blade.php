<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BrewHub Order Receipt</title>
</head>
<body style="margin:0;padding:0;background:#F3E9D7;font-family:Arial,sans-serif;color:#3A2E22;">
    @php
        $pickupDateRaw = (string) ($receiptMeta['pickup_date'] ?? '');
        $pickupTimeRaw = (string) ($receiptMeta['pickup_time'] ?? '');

        $pickupDateDisplay = 'N/A';
        if ($pickupDateRaw !== '') {
            try {
                $pickupDateDisplay = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $pickupDateRaw)->format('M d, Y');
            } catch (\Throwable $exception) {
                $pickupDateDisplay = $pickupDateRaw;
            }
        }

        $pickupTimeDisplay = 'N/A';
        if ($pickupTimeRaw !== '') {
            try {
                $pickupTimeDisplay = \Illuminate\Support\Carbon::createFromFormat('H:i', $pickupTimeRaw)->format('h:i A');
            } catch (\Throwable $exception) {
                $pickupTimeDisplay = $pickupTimeRaw;
            }
        }
    @endphp
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#F3E9D7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #E6DDCF;">
                    <tr>
                        <td style="background:#3A2E22;color:#ffffff;padding:20px 24px;">
                            <p style="margin:0;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;opacity:0.85;">BrewHub</p>
                            <h1 style="margin:8px 0 0 0;font-size:24px;line-height:1.25;">{{ $eventType === 'status_updated' ? 'Order Status Update' : 'Reservation Receipt' }}</h1>
                            <p style="margin:8px 0 0 0;font-size:13px;opacity:0.85;">Seller: {{ $order->product?->establishment?->name ?? 'Verified Farm Seller' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px;">
                            <p style="margin:0 0 12px 0;font-size:14px;line-height:1.6;">
                                {{ $eventType === 'status_updated' ? 'Your order status has been updated.' : 'Your reservation has been recorded successfully.' }}
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:1px solid #E2D5C1;border-radius:8px;overflow:hidden;">
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-bottom:1px solid #E2D5C1;width:160px;">Reservation ID</td>
                                    <td style="padding:10px 12px;font-size:13px;border-bottom:1px solid #E2D5C1;">{{ $reservationCode }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-bottom:1px solid #E2D5C1;">Status</td>
                                    <td style="padding:10px 12px;font-size:13px;border-bottom:1px solid #E2D5C1;">{{ ucfirst((string) $order->status) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-bottom:1px solid #E2D5C1;">Product</td>
                                    <td style="padding:10px 12px;font-size:13px;border-bottom:1px solid #E2D5C1;">{{ $order->product?->name ?? ($receiptMeta['product_name'] ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-bottom:1px solid #E2D5C1;">Quantity</td>
                                    <td style="padding:10px 12px;font-size:13px;border-bottom:1px solid #E2D5C1;">{{ (int) $order->quantity }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;">Total</td>
                                    <td style="padding:10px 12px;font-size:13px;">PHP {{ number_format((float) $order->total_price, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-top:1px solid #E2D5C1;">Pickup Date</td>
                                    <td style="padding:10px 12px;font-size:13px;border-top:1px solid #E2D5C1;">{{ $pickupDateDisplay }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;font-size:13px;color:#946042;border-top:1px solid #E2D5C1;">Estimated Pickup Time</td>
                                    <td style="padding:10px 12px;font-size:13px;border-top:1px solid #E2D5C1;">{{ $pickupTimeDisplay }}</td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0 0;">
                                <a href="{{ $receiptUrl }}" style="display:inline-block;background:#2E5A3D;color:#ffffff;text-decoration:none;padding:10px 14px;border-radius:8px;font-size:13px;">View Official Receipt</a>
                            </p>

                            @php
                                $ratingParams = ['order' => $order->id];
                                if (!empty($receiptMeta['receipt_token'])) {
                                    $ratingParams['token'] = $receiptMeta['receipt_token'];
                                }
                                $ratingUrl = route('reservations.orders.rating.form', $ratingParams);
                                $isCancelledOrder = in_array(strtolower((string) $order->status), ['cancelled', 'canceled'], true);
                            @endphp

                            @if (! $order->productRating && ! $isCancelledOrder)
                                <p style="margin:12px 0 0 0;">
                                    <a href="{{ $ratingUrl }}" style="display:inline-block;background:#8B5E34;color:#ffffff;text-decoration:none;padding:10px 14px;border-radius:8px;font-size:13px;">Rate This Product</a>
                                </p>
                            @elseif ($order->productRating)
                                <p style="margin:12px 0 0 0;font-size:12px;color:#2E5A3D;line-height:1.6;">
                                    Thanks for rating this product on {{ optional($order->productRating->created_at)->format('M d, Y') }}.
                                </p>
                            @endif

                            <p style="margin:16px 0 0 0;font-size:12px;color:#6B5B4A;line-height:1.6;">
                                Customer: {{ $receiptMeta['full_name'] ?? ($order->user?->name ?? 'N/A') }}<br>
                                Address: {{ $receiptMeta['address'] ?? 'N/A' }}<br>
                                Phone: {{ $receiptMeta['phone'] ?? 'N/A' }}<br>
                                Pickup Date: {{ $pickupDateDisplay }}<br>
                                Estimated Pickup Time: {{ $pickupTimeDisplay }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
