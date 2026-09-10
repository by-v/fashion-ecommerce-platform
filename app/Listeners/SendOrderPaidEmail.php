<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;

class SendOrderPaidEmail implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        // Re-fetch order dengan semua relasi yang dibutuhkan
        $order = Order::with('items', 'user', 'shippingMethod')
            ->find($event->order->id);

        if (!$order || !$order->user) {
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