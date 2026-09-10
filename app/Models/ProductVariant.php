<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saved(function ($variant) {
            $variant->syncProductStock();
        });

        static::deleted(function ($variant) {
            $variant->syncProductStock();
        });
    }

    public function syncProductStock(): void
    {
        if ($this->product) {
            $totalStock = $this->product->variants()->sum('stock');
            $this->product->update(['stock' => $totalStock]);
        }
    }
}