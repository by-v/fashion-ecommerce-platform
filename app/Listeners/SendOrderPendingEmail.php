<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPendingMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderPendingEmail implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 60;

    public function handle(OrderCreated $event): void
    {
        // Re-load order dari database dengan relasi lengkap
        // supaya tidak bergantung pada data yang mungkin tidak ter-serialize di queue
        $order = Order::with('items', 'user', 'shippingMethod')
            ->find($event->order->id);

        if (! $order || ! $order->user) {
            return;
        }

        Mail::to($order->user->email)
            ->send(new OrderPendingMail($order));
    }
}
