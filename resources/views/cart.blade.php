@extends('layouts.shop')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <h1 class="text-2xl font-bold mb-8">Keranjang Belanja</h1>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 text-sm px-4 py-3 mb-6 rounded">
                {{ session('success') }}
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
                    <div class="flex gap-4 border-b border-gray-200 pb-6">
                        <div class="w-24 h-24 bg-gray-100 flex-shrink-0">
                            @if ($item->product->image)
                                <img src="{{ Storage::url($item->product->image) }}" class="w-full h-full object-cover">
                            @endif
                        </div>

                        <div class="flex-1">
                            <h3 class="font-medium">{{ $item->product->name }}</h3>
                            @if ($item->variant)
                                <p class="text-sm text-gray-500">Size: {{ $item->variant->size }}</p>
                            @endif
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
                        </div>

                        <div class="text-sm font-medium">
                            Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex justify-between items-center">
                <div class="text-lg font-semibold">
                    Subtotal: Rp
                    {{ number_format($cart->items->sum(fn($i) => $i->product->price * $i->quantity), 0, ',', '.') }}
                </div>
                <a href="{{ route('checkout.cart') }}"
                    class="bg-black text-white px-10 py-3 text-sm uppercase tracking-widest hover:bg-gray-800">
                    Checkout
                </a>
            </div>
        @endif

    </div>
@endsection
