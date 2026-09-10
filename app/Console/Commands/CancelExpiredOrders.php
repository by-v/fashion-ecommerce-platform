<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel pending orders that have passed their payment deadline and restore stock';

    public function handle(): void
    {
        $expiredOrders = Order::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with('items.product', 'items.variant')
            ->get();

        foreach ($expiredOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->increment('stock', $item->quantity);
                    $item->variant?->syncProductStock();
                } else {
                    $item->product?->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        }

        $this->info("Cancelled {$expiredOrders->count()} expired orders and restored their stock.");
    }
}
