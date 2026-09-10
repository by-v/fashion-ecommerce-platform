<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Events\OrderPaid;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getSnapToken(string $orderNumber)
    {
        // Di dalam fungsi getSnapToken:
        $order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Kalau order sudah dibayar, tidak perlu generate token lagi
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order ini sudah tidak bisa dibayar.'], 400);
        }

        $itemDetails = $order->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => substr($item->product_name . ($item->size ? " ({$item->size})" : ''), 0, 50),
            ];
        })->toArray();

        // Tambahkan ongkos kirim sebagai item terpisah
        $itemDetails[] = [
            'id' => 'shipping',
            'price' => (int) $order->shipping_cost,
            'quantity' => 1,
            'name' => 'Ongkos Kirim',
        ];
        $duration = (int) now()->diffInMinutes($order->payment_deadline, false);

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'phone' => $order->phone,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => max($duration, 1), // minimal 1 menit, jaga-jaga kalau sudah lewat
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json(['snap_token' => $snapToken]);
    }

    // Dipanggil Midtrans otomatis (webhook) saat status pembayaran berubah
    public function notification()
    {
        $notif = new \Midtrans\Notification();

        $orderNumber = $notif->order_id;
        $statusCode = $notif->status_code;
        $grossAmount = $notif->gross_amount;
        $signatureKey = $notif->signature_key ?? '';
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;

        // 1. Validasi Signature Key
        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans webhook invalid signature key', [
                'order_id' => $orderNumber,
                'signature_key' => $signatureKey,
            ]);

            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept' || $fraudStatus === null) {
                // 2. Validasi gross_amount
                if (abs((float) $grossAmount - (float) $order->total) > 0.01) {
                    Log::warning('Midtrans webhook gross amount mismatch', [
                        'order_id' => $orderNumber,
                        'received_amount' => $grossAmount,
                        'expected_total' => $order->total,
                    ]);

                    return response()->json(['message' => 'Gross amount mismatch'], 400);
                }

                // Hindari pemrosesan ganda jika status sudah paid
                if ($order->status === 'paid') {
                    return response()->json(['message' => 'OK']);
                }

                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                $order->payment()->create([
                    'payment_gateway_id' => $notif->transaction_id,
                    'method' => $notif->payment_type,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);

                event(new OrderPaid($order));
            }
        } elseif ($transactionStatus === 'expire') {
            $this->restoreStock($order);
            $order->update(['status' => 'cancelled']);
        } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny') {
            $this->restoreStock($order);
            $order->update(['status' => 'cancelled']);
        }

        return response()->json(['message' => 'OK']);
    }

    private function restoreStock(Order $order): void
    {
        // Hindari restore dobel kalau notification terkirim lebih dari sekali
        if ($order->status === 'cancelled') {
            return;
        }

        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $item->variant?->increment('stock', $item->quantity);
                $item->variant?->syncProductStock();
            } else {
                $item->product?->increment('stock', $item->quantity);
            }
        }
    }
}
