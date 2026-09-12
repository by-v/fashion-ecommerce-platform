<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'recipient_name',
        'phone',
        'country',
        'city',
        'address_detail',
        'shipping_method_id',
        'shipping_cost',
        'subtotal',
        'total',
        'payment_deadline',
        'paid_at',
        'shipped_at',
        'tracking_number',
    ];

    public static function getStatusTransitions(string $status): array
    {
        $transitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['processed', 'cancelled'],
            'processed' => ['shipped'],
            'shipped' => ['completed'],
            'cancelled' => [],
            'completed' => [],
        ];

        return $transitions[$status] ?? [];
    }

    public function getStatusTransitionOptions(): array
    {
        $allowedStatuses = self::getStatusTransitions($this->status);

        return array_combine(
            $allowedStatuses,
            array_map(fn ($status) => match ($status) {
                'pending' => 'Menunggu Pembayaran',
                'paid' => 'Sudah Dibayar',
                'processed' => 'Diproses',
                'shipped' => 'Dikirim',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            }, $allowedStatuses)
        );
    }

    public static function restoreStockForOrder(self $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.product', 'items.variant');

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->increment('stock', $item->quantity);
                    $item->variant?->syncProductStock();
                } else {
                    $item->product?->increment('stock', $item->quantity);
                }
            }
        });
    }

    protected $casts = [
        'payment_deadline' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'shipping_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
