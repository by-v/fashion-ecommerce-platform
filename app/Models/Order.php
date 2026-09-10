<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
