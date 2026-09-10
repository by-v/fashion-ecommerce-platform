<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Events\OrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Checkout dari Cart (multi produk)
    public function fromCart()
    {
        $cart = Auth::user()->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Keranjang Anda kosong.');
        }

        $items = $cart->items->load('product', 'variant');
        $subtotal = $items->sum(fn($i) => $i->product->price * $i->quantity);

        session(['checkout_type' => 'cart']);

        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        session(['checkout_submission_token' => \Illuminate\Support\Str::uuid()->toString()]);

        return view('checkout', compact('items', 'subtotal', 'shippingMethods'));
    }

    // Checkout langsung dari Detail Produk (Beli Sekarang)
    public function buyNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);
        $variant = $request->product_variant_id
            ? $product->variants->find($request->product_variant_id)
            : null;

        // Simpan sementara di session untuk dipakai saat submit order
        session([
            'checkout_type' => 'buy_now',
            'buy_now_data' => [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $request->quantity,
            ],
        ]);

        $items = collect([
            (object) [
                'product' => $product,
                'variant' => $variant,
                'quantity' => $request->quantity,
            ],
        ]);

        $subtotal = $product->price * $request->quantity;
        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        return view('checkout', compact('items', 'subtotal', 'shippingMethods'));
    }

    // Proses submit form alamat + buat order
    public function store(Request $request)
    {
        $lock = Cache::lock('user_checkout_'.auth()->id(), 10);

        if (! $lock->get()) {
            return back()->with('error', 'Pesanan sedang diproses, mohon tunggu sebentar.');
        }

        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address_detail' => 'required|string',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ]);

        $checkoutType = session('checkout_type');
        $shippingMethod = ShippingMethod::findOrFail($request->shipping_method_id);

        try {
            $order = DB::transaction(function () use ($request, $shippingMethod, $checkoutType) {
                // Siapkan daftar item dan lakukan pessimistic locking
                if ($checkoutType === 'buy_now') {
                    $data = session('buy_now_data');
                    $product = Product::lockForUpdate()->findOrFail($data['product_id']);
                    $variant = $data['product_variant_id']
                        ? $product->variants()->lockForUpdate()->find($data['product_variant_id'])
                        : null;

                    $itemsToOrder = collect([
                        (object) [
                            'product' => $product,
                            'variant' => $variant,
                            'quantity' => $data['quantity'],
                        ],
                    ]);
                } else {
                    $cart = Auth::user()->cart;
                    if (!$cart) {
                        throw new \Exception('Keranjang tidak ditemukan.');
                    }
                    $itemsToOrder = $cart->items->load('product', 'variant');

                    foreach ($itemsToOrder as $item) {
                        if ($item->variant_id ?? $item->product_variant_id) {
                            $variantId = $item->product_variant_id;
                            $item->unsetRelation('variant');
                            $item->variant = \App\Models\ProductVariant::lockForUpdate()->find($variantId);
                        } else {
                            $productId = $item->product_id;
                            $item->unsetRelation('product');
                            $item->product = Product::lockForUpdate()->find($productId);
                        }
                    }
                }

                if ($itemsToOrder->isEmpty()) {
                    throw new \Exception('Tidak ada produk untuk di-checkout.');
                }

                // Validasi stok dengan lock
                foreach ($itemsToOrder as $item) {
                    $availableStock = $item->variant ? $item->variant->stock : $item->product->stock;

                    if ($availableStock < $item->quantity) {
                        $productLabel = $item->product->name . ($item->variant ? " (Size {$item->variant->size})" : '');
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'stock' => ["Stok {$productLabel} tidak mencukupi. Sisa stok: {$availableStock}."]
                        ]);
                    }
                }

                $subtotal = $itemsToOrder->sum(fn($i) => $i->product->price * $i->quantity);
                $total = $subtotal + $shippingMethod->cost;

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'order_number' => strtoupper(Str::random(10)),
                    'status' => 'pending',
                    'recipient_name' => $request->recipient_name,
                    'phone' => $request->phone,
                    'country' => $request->country,
                    'city' => $request->city,
                    'address_detail' => $request->address_detail,
                    'shipping_method_id' => $shippingMethod->id,
                    'shipping_cost' => $shippingMethod->cost,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'payment_deadline' => now()->addMinutes(10),
                ]);

                foreach ($itemsToOrder as $item) {
                    $order->items()->create([
                        'product_id' => $item->product->id,
                        'product_variant_id' => $item->variant?->id,
                        'product_name' => $item->product->name,
                        'size' => $item->variant?->size,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->product->price * $item->quantity,
                    ]);

                    // Kurangi stok
                    if ($item->variant) {
                        $item->variant->decrement('stock', $item->quantity);
                        $item->variant->syncProductStock();
                    } else {
                        $item->product->decrement('stock', $item->quantity);
                    }
                }

                // Kalau checkout dari cart, kosongkan cart setelah order dibuat
                if ($checkoutType === 'cart') {
                    Auth::user()->cart->items()->delete();
                }

                return $order;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            $lock->release();

            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            $lock->release();

            return back()->with('error', $e->getMessage());
        }

        event(new OrderCreated($order));

        session()->forget(['checkout_type', 'buy_now_data']);

        $lock->release();

        return redirect()->route('order.confirmation', $order->order_number);
    }
}
