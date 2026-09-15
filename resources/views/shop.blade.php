@extends('layouts.shop')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <h1 class="text-2xl font-bold mb-8">Semua Produk</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">

            <!-- Sidebar Filter -->
            <aside class="md:col-span-1">
                <form method="GET" action="{{ route('shop') }}" id="filterForm">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    @php
                        $chip = 'inline-flex items-center px-3 py-1.5 text-sm border rounded-full transition cursor-pointer select-none whitespace-nowrap bg-white text-gray-700 border-gray-300 hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black peer-focus-visible:ring-2 peer-focus-visible:ring-black/30';
                    @endphp

                    <div class="grid grid-cols-2 gap-x-4 gap-y-6 md:grid-cols-1">

                        <!-- Kategori -->
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Kategori</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($categories as $category)
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                            onchange="document.getElementById('filterForm').submit()"
                                            {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                            class="peer sr-only">
                                        <span class="{{ $chip }}">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tipe Produk -->
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Tipe Produk</h3>
                            <div class="flex flex-wrap gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="all"
                                        onchange="document.getElementById('filterForm').submit()"
                                        {{ request('type', 'all') === 'all' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="{{ $chip }}">Semua Produk</span>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="featured"
                                        onchange="document.getElementById('filterForm').submit()"
                                        {{ request('type') === 'featured' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="{{ $chip }}">Produk Unggulan</span>
                                </label>
                            </div>
                        </div>

                        <!-- Ketersediaan -->
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Ketersediaan</h3>
                            <div class="flex flex-wrap gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="availability" value="all"
                                        onchange="document.getElementById('filterForm').submit()"
                                        {{ request('availability', 'all') === 'all' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="{{ $chip }}">Semua</span>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="availability" value="in_stock"
                                        onchange="document.getElementById('filterForm').submit()"
                                        {{ request('availability') === 'in_stock' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="{{ $chip }}">Stok Tersedia</span>
                                </label>
                            </div>
                        </div>

                        <!-- Size (khusus fashion, kosongkan array $availableSizes untuk toko non-fashion) -->
                        @if (count($availableSizes))
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Size</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($availableSizes as $size)
                                        <label class="relative cursor-pointer">
                                            <input type="checkbox" name="sizes[]" value="{{ $size }}"
                                                onchange="document.getElementById('filterForm').submit()"
                                                {{ in_array($size, request('sizes', [])) ? 'checked' : '' }}
                                                class="peer sr-only">
                                            <span class="{{ $chip }}">{{ $size }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                </form>
            </aside>

            <!-- Product Grid -->
            <div class="md:col-span-3">
                @if ($products->isEmpty())
                    <p class="text-gray-500 text-center py-20">Tidak ada produk ditemukan.</p>
                @else
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            <a href="{{ route('product.show', $product->slug) }}" class="group">
                                <div class="aspect-square bg-gray-100 overflow-hidden mb-3">
                                    @if ($product->image)
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                                            Belum ada gambar
                                        </div>
                                    @endif
                                </div>
                                <h3 class="text-sm font-medium mb-1">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                @if ($product->stock <= 0)
                                    <p class="text-xs text-red-500 mt-1">Stok Habis</p>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
