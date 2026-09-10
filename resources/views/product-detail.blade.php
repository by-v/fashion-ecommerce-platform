@extends('layouts.shop')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="grid md:grid-cols-2 gap-12">

            <!-- Product Image -->
            <div class="aspect-square bg-gray-100">
                @if ($product->image)
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                        class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        Belum ada gambar
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div>
                @if ($product->stock > 0)
                    <p class="text-sm text-green-600 mb-2">Stok Tersedia ({{ $product->stock }})</p>
                @else
                    <p class="text-sm text-red-500 mb-2">Stok Habis</p>
                @endif

                <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>

                <div class="flex items-center gap-1 mb-4 text-yellow-500">
                    @for ($i = 0; $i < 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                            <path
                                d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                        </svg>
                    @endfor
                    <span class="text-sm text-gray-500 ml-1">(4.8)</span>
                </div>

                <p class="text-2xl font-semibold mb-6">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </p>

                <p class="text-gray-600 text-sm mb-8 leading-relaxed">
                    {{ $product->description }}
                </p>

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <!-- Size Selector -->
                    @if ($product->variants->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Pilih Size</h3>
                            <div class="flex gap-2 flex-wrap">
                                @foreach ($product->variants as $variant)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="product_variant_id" value="{{ $variant->id }}"
                                            {{ $variant->stock <= 0 ? 'disabled' : '' }}
                                            {{ $loop->first && $variant->stock > 0 ? 'checked' : '' }}
                                            class="peer sr-only">
                                        <span
                                            class="block px-4 py-2 border border-gray-300 text-sm
                        peer-checked:bg-black peer-checked:text-white peer-checked:border-black
                        {{ $variant->stock <= 0 ? 'opacity-40 line-through' : 'hover:border-black' }}">
                                            {{ $variant->size }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quantity Stepper -->
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Jumlah</h3>
                        <div class="flex items-center border border-gray-300 w-fit">
                            <button type="button" onclick="updateQty(-1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-100">-</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1"
                                max="{{ $product->variants->isNotEmpty() ? ($product->variants->first()->stock ?: 1) : $product->stock }}"
                                readonly
                                class="w-14 h-10 text-center border-x border-gray-300 focus:outline-none bg-gray-50 cursor-not-allowed">
                            <button type="button" onclick="updateQty(1)"
                                class="w-10 h-10 flex items-center justify-center hover:bg-gray-100">+</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 border border-black text-black py-3 text-sm uppercase tracking-widest font-medium hover:bg-gray-100 transition">
                            Tambah Keranjang
                        </button>
                        <button type="submit" formaction="{{ route('checkout.buyNow') }}"
                            class="flex-1 bg-black text-white py-3 text-sm uppercase tracking-widest font-medium hover:bg-gray-800 transition">
                            Beli Sekarang
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Data stok per-variant, dikirim dari server ke JS
        const variantStocks = {
            @foreach ($product->variants as $variant)
                {{ $variant->id }}: {{ $variant->stock }},
            @endforeach
        };
        const productStock = {{ $product->stock }};

        function getCurrentMaxStock() {
            const checkedVariant = document.querySelector('input[name="product_variant_id"]:checked');
            if (checkedVariant) {
                return variantStocks[checkedVariant.value] ?? 0;
            }
            return productStock;
        }

        function updateQty(change) {
            const input = document.getElementById('qtyInput');
            const max = getCurrentMaxStock();
            let value = parseInt(input.value) + change;
            if (value < 1) value = 1;
            if (value > max) value = max;
            input.value = value;
            input.max = max;
        }

        // Setiap ganti pilihan size, reset quantity & update batas maksimal
        document.querySelectorAll('input[name="product_variant_id"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const input = document.getElementById('qtyInput');
                const max = getCurrentMaxStock();
                input.max = max;
                input.value = 1;
            });
        });

        // Set max awal saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('qtyInput');
            input.max = getCurrentMaxStock();
        });
    </script>
@endsection
