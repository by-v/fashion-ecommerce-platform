<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CompleteDeliveredOrders extends Command
{
    protected $signature = 'orders:complete-delivered';
    protected $description = 'Mark shipped orders as completed once estimated delivery time has passed';

    public function handle(): void
    {
        $orders = Order::where('status', 'shipped')
            ->whereNotNull('shipped_at')
            ->with('shippingMethod')
            ->get();

        $completed = 0;

        foreach ($orders as $order) {
            $estimatedDays = $order->shippingMethod->estimated_days ?? 3;

            if ($order->shipped_at->addDays($estimatedDays)->isPast()) {
                $order->update(['status' => 'completed']);
                $completed++;
            }
        }

        $this->info("Marked {$completed} orders as completed.");
    }
}
