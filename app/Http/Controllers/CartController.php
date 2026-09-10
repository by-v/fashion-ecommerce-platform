<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Helper: ambil atau buat cart milik user yang login
    private function getOrCreateCart()
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }

    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->load('items.product', 'items.variant');

        return view('cart', compact('cart'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getOrCreateCart();

        // Cek apakah item (produk + size sama) sudah ada di cart, kalau ada tambah qty-nya
        $existingItem = $cart->items()
            ->where('product_id', $request->product_id)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $request->quantity);
        } else {
            $cart->items()->create([
                'product_id' => $request->product_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        return redirect()->route('cart')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function updateQuantity(Request $request, $itemId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart = $this->getOrCreateCart();
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => $request->quantity]);

        return back();
    }

    public function remove($itemId)
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->where('id', $itemId)->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
