<?php

namespace App\Observers;

use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function updating(Order $order): void
    {
        // Kirim email saat status pertama kali berubah jadi "shipped"
        if ($order->isDirty('status') && $order->getOriginal('status') !== 'shipped' && $order->status === 'shipped') {

            // Set shipped_at kalau belum diisi
            if (! $order->shipped_at) {
                $order->shipped_at = now();
            }

            // Kirim email ke user (dispatch ke queue)
            dispatch(function () use ($order) {
                $freshOrder = Order::with('items', 'user', 'shippingMethod')
                    ->find($order->id);

                if ($freshOrder && $freshOrder->user) {
                    Mail::to($freshOrder->user->email)
                        ->send(new OrderShippedMail($freshOrder));
                }
            })->afterCommit();
        }
    }
}
