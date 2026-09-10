<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function confirmation(string $orderNumber)
    {
        $order = Order::with('items.product', 'items.variant', 'shippingMethod')
            ->where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->expireIfNeeded($order);

        return view('order-confirmation', compact('order'));
    }

    private function expireIfNeeded(Order $order): void
    {
        if ($order->status !== 'pending' || $order->payment_deadline->isFuture()) {
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

        $order->update(['status' => 'cancelled']);
        $order->refresh();
    }
}
