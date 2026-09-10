@extends('layouts.shop')

@section('content')
    <!-- Hero Banner -->
    <div class="relative w-full h-[80vh] min-h-[500px] bg-gray-100">

        @if ($heroProduct && $heroProduct->image)
            <img src="{{ Storage::url($heroProduct->image) }}" alt="{{ $heroProduct->name }}"
                class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                Belum ada gambar
            </div>
        @endif

        <!-- Overlay agar tombol tetap terlihat jelas di gambar apapun -->
        <div class="absolute inset-0 bg-black/10"></div>

        <!-- Shop Now Button (Centered) -->
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <a href="{{ route('shop') }}"
                class="bg-black text-white px-10 py-4 text-sm tracking-widest uppercase font-medium hover:bg-gray-800 transition shadow-lg">
                Shop Now
            </a>
        </div>

    </div>

    <!-- Lacak Pesanan & Customer Service -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
        <div class="flex items-center justify-center gap-6 text-sm">
            <a href="{{ route('track.order') }}" class="text-gray-600 hover:text-black hover:underline">
                Lacak Pesanan
            </a>
            <span class="text-gray-300">|</span>
            <a href="#" class="text-gray-600 hover:text-black hover:underline">
                Customer Service
            </a>
        </div>
    </div>
@endsection
