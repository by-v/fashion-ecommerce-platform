<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Cancel pending orders that have passed their payment deadline and restore stock';

    public function handle(): void
    {
        $expiredOrderIds = Order::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->pluck('id');

        $cancelledCount = 0;

        foreach ($expiredOrderIds as $orderId) {
            try {
                $processed = DB::transaction(function () use ($orderId) {
                    $order = Order::lockForUpdate()->find($orderId);

                    // Re-check status setelah lock didapat untuk cegah race condition dengan webhook payment
                    if (! $order || $order->status !== 'pending') {
                        return false;
                    }

                    $order->load('items.product', 'items.variant');

                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            $item->variant?->increment('stock', $item->quantity);
                            $item->variant?->syncProductStock();
                        } else {
                            $item->product?->increment('stock', $item->quantity);
                        }
                    }

                    $order->update(['status' => 'cancelled']);

                    return true;
                });

                if ($processed) {
                    $cancelledCount++;
                }
            } catch (\Exception $e) {
                Log::error("Gagal membatalkan order expired ID {$orderId}: ".$e->getMessage());
            }
        }

        $this->info("Cancelled {$cancelledCount} expired orders and restored their stock.");
    }
}
