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

        // Bersihkan otomatis item jika record produknya sudah dihapus dari database
        $cart->items()->whereDoesntHave('product')->delete();

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

        $product = Product::findOrFail($request->product_id);

        if (! $product->is_active) {
            return back()->with('error', "Produk {$product->name} saat ini tidak tersedia.");
        }

        $variant = $request->product_variant_id
            ? ProductVariant::where('product_id', $product->id)->find($request->product_variant_id)
            : null;

        if ($request->product_variant_id && ! $variant) {
            return back()->with('error', 'Varian ukuran yang dipilih tidak ditemukan.');
        }

        $availableStock = $variant ? $variant->stock : $product->stock;
        $label = $product->name.($variant ? " (Size {$variant->size})" : '');

        $cart = $this->getOrCreateCart();

        // Cek apakah item (produk + size sama) sudah ada di cart
        $existingItem = $cart->items()
            ->where('product_id', $request->product_id)
            ->where('product_variant_id', $request->product_variant_id)
            ->first();

        $currentQtyInCart = $existingItem ? $existingItem->quantity : 0;
        $requestedTotal = $currentQtyInCart + $request->quantity;

        if ($requestedTotal > $availableStock) {
            return back()->with('error', "Stok {$label} tidak mencukupi. Sisa stok tersedia: {$availableStock}.");
        }

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
        $item = $cart->items()->with('product', 'variant')->findOrFail($itemId);

        if (! $item->product || ! $item->product->is_active) {
            return back()->with('error', 'Produk ini sudah tidak tersedia.');
        }

        if ($item->product_variant_id && ! $item->variant) {
            return back()->with('error', 'Varian ukuran untuk produk ini sudah tidak tersedia.');
        }

        $availableStock = $item->variant ? $item->variant->stock : $item->product->stock;
        $label = $item->product->name.($item->variant ? " (Size {$item->variant->size})" : '');

        if ($request->quantity > $availableStock) {
            return back()->with('error', "Stok {$label} tidak mencukupi. Sisa stok tersedia: {$availableStock}.");
        }

        $item->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function remove($itemId)
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->where('id', $itemId)->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
