<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        if (! $this->product) {
            return;
        }

        DB::transaction(function () {
            $product = Product::lockForUpdate()->findOrFail($this->product_id);
            $totalStock = $product->variants()->sum('stock');
            $product->update(['stock' => $totalStock]);
        });
    }
}
