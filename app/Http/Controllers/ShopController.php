<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['nullable', 'exists:categories,id'],
            'type' => ['nullable', 'string', 'in:featured'],
            'availability' => ['nullable', 'string', 'in:in_stock'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'string'],
        ]);

        $query = Product::with('category', 'variants');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', $request->search.'%');
        }

        // Filter kategori (checkbox, bisa banyak)
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }

        // Filter tipe produk
        if ($request->type === 'featured') {
            $query->where('is_featured', true);
        }

        // Filter ketersediaan
        if ($request->availability === 'in_stock') {
            $query->where('stock', '>', 0);
        }

        // Filter size
        if ($request->filled('sizes')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->whereIn('size', $request->sizes);
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

        $availableSizes = Cache::remember('shop_available_sizes', 60, function () {
            $standardOrder = [
                'XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'All Size',
                '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '42',
            ];

            return ProductVariant::whereNotNull('size')
                ->where('size', '!=', '')
                ->distinct()
                ->pluck('size')
                ->sort(function ($a, $b) use ($standardOrder) {
                    $posA = array_search($a, $standardOrder);
                    $posB = array_search($b, $standardOrder);
                    if ($posA !== false && $posB !== false) {
                        return $posA <=> $posB;
                    }
                    if ($posA !== false) {
                        return -1;
                    }
                    if ($posB !== false) {
                        return 1;
                    }

                    return strnatcasecmp($a, $b);
                })
                ->values()
                ->toArray();
        });

        return view('shop', compact('products', 'categories', 'availableSizes'));
    }
}
