<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendOrderPaidEmail implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 60;

    public function handle(OrderPaid $event): void
    {
        $cacheKey = 'order_paid_email_sent_'.$event->order->id;

        // Cegah pengiriman email ganda jika event OrderPaid ter-fire berulang
        if (! Cache::add($cacheKey, true, now()->addHours(24))) {
            return;
        }

        // Re-fetch order dengan semua relasi yang dibutuhkan
        $order = Order::with('items', 'user', 'shippingMethod')
            ->find($event->order->id);

        if (! $order || ! $order->user) {
            return;
        }

        // 1. Email ke pembeli: Pembayaran Berhasil
        Mail::to($order->user->email)
            ->send(new OrderPaidMail($order));

        // 2. Email ke admin/toko: Ada Pesanan Baru
        $adminEmail = config('services.admin_email');
        if ($adminEmail) {
            Mail::to($adminEmail)
                ->send(new AdminNewOrderMail($order));
        }
    }
}
