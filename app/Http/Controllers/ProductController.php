<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with('variants', 'category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('product-detail', compact('product'));
    }
}
