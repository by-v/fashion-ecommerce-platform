<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'cost', 'estimated_days', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'cost' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
