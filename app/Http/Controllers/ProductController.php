<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with('variants', 'category')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('product-detail', compact('product'));
    }
}
