<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPendingMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;

class SendOrderPendingEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        // Re-load order dari database dengan relasi lengkap
        // supaya tidak bergantung pada data yang mungkin tidak ter-serialize di queue
        $order = Order::with('items', 'user', 'shippingMethod')
            ->find($event->order->id);

        if (!$order || !$order->user) {
            return;
        }

        Mail::to($order->user->email)
            ->send(new OrderPendingMail($order));
    }
}