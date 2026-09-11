@extends('layouts.shop')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <h1 class="text-2xl font-bold mb-8">Keranjang Belanja</h1>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm px-4 py-3 mb-6 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-700 text-sm px-4 py-3 mb-6 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if ($cart->items->isEmpty())
            <div class="text-center py-20">
                <p class="text-gray-500 mb-6">Keranjang Anda masih kosong.</p>
                <a href="{{ route('shop') }}"
                    class="inline-block bg-black text-white px-8 py-3 text-sm uppercase tracking-widest hover:bg-gray-800">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($cart->items as $item)
                    @php
                        $isAvailable = $item->product && $item->product->is_active && (!$item->product_variant_id || $item->variant);
                    @endphp
                    <div class="flex gap-4 border-b border-gray-200 pb-6 {{ !$isAvailable ? 'opacity-60 bg-gray-50 p-4 rounded' : '' }}">
                        <div class="w-24 h-24 bg-gray-100 flex-shrink-0 flex items-center justify-center">
                            @if ($item->product?->image)
                                <img src="{{ Storage::url($item->product->image) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-xs text-gray-400">No Image</span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <h3 class="font-medium">{{ $item->product?->name ?? 'Produk tidak tersedia' }}</h3>
                            @if ($item->variant)
                                <p class="text-sm text-gray-500">Size: {{ $item->variant->size }}</p>
                            @endif

                            @if ($isAvailable)
                                <p class="text-sm text-gray-600 mt-1">
                                    Rp {{ number_format($item->product->price, 0, ',', '.') }}
                                </p>

                                <div class="flex items-center gap-4 mt-3">
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST"
                                        class="flex items-center border border-gray-300 w-fit">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                            onchange="this.form.submit()"
                                            class="w-14 h-9 text-center text-sm focus:outline-none">
                                    </form>

                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            @else
                                <p class="text-sm text-red-600 font-semibold mt-1">
                                    Produk ini sedang tidak tersedia atau telah dinonaktifkan.
                                </p>
                                <div class="mt-3">
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-500 hover:underline">Hapus dari keranjang</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="text-sm font-medium">
                            @if ($isAvailable)
                                Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $hasUnavailable = $cart->items->contains(function ($i) {
                    return !$i->product || !$i->product->is_active || ($i->product_variant_id && !$i->variant);
                });
                $subtotal = $cart->items->filter(function ($i) {
                    return $i->product && $i->product->is_active && (!$i->product_variant_id || $i->variant);
                })->sum(fn($i) => $i->product->price * $i->quantity);
            @endphp

            <div class="mt-10 flex justify-between items-center">
                <div class="text-lg font-semibold">
                    Subtotal: Rp {{ number_format($subtotal, 0, ',', '.') }}
                </div>
                @if ($hasUnavailable)
                    <button disabled
                        title="Hapus produk yang tidak tersedia terlebih dahulu"
                        class="bg-gray-400 cursor-not-allowed text-white px-10 py-3 text-sm uppercase tracking-widest">
                        Checkout
                    </button>
                @else
                    <a href="{{ route('checkout.cart') }}"
                        class="bg-black text-white px-10 py-3 text-sm uppercase tracking-widest hover:bg-gray-800">
                        Checkout
                    </a>
                @endif
            </div>
        @endif

    </div>
@endsection
