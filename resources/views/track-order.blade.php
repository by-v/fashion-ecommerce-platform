@extends('layouts.shop')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        <h1 class="text-2xl font-bold mb-2 text-center">Lacak Pesanan</h1>
        <p class="text-sm text-gray-500 text-center mb-8">
            Masukkan nomor order Anda untuk melihat status dan informasi pengiriman.
        </p>

        <!-- Form Lacak -->
        <form action="{{ route('track.order.search') }}" method="POST" class="mb-10">
            @csrf
            <div class="flex gap-3">
                <input type="text" name="order_number" placeholder="Contoh: ABCD1234EF"
                    value="{{ request()->old('order_number') ?? (isset($order) ? $order->order_number : '') }}"
                    class="flex-1 border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-black uppercase">
                <button type="submit"
                    class="bg-black text-white px-6 py-3 text-sm uppercase tracking-widest hover:bg-gray-800 transition">
                    Cari
                </button>
            </div>
        </form>

        <!-- Hasil Pencarian -->
        @if (isset($order))
            @if ($order)

                <!-- Status Timeline -->
                <div class="border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide">
                            Order #{{ $order->order_number }}
                        </h2>
                        <span
                            class="text-xs px-3 py-1 rounded-full font-medium
                        @if ($order->status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($order->status === 'paid') bg-blue-100 text-blue-700
                        @elseif($order->status === 'processed') bg-purple-100 text-purple-700
                        @elseif($order->status === 'shipped') bg-orange-100 text-orange-700
                        @elseif($order->status === 'completed') bg-green-100 text-green-700
                        @else bg-red-100 text-red-700 @endif">
                            @php
                                $statusLabels = [
                                    'pending' => 'Menunggu Pembayaran',
                                    'paid' => 'Sudah Dibayar',
                                    'processed' => 'Sedang Diproses',
                                    'shipped' => 'Sedang Dikirim',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                ];
                            @endphp
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>

                    <!-- Info Pengiriman (muncul kalau sudah dikirim) -->
                    @if ($order->status === 'shipped' || $order->status === 'completed')
                        <div class="bg-gray-50 p-4 mb-4 text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Ekspedisi</span>
                                <span class="font-medium">{{ $order->shippingMethod->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nomor Resi</span>
                                <span class="font-medium">{{ $order->tracking_number ?? 'Belum tersedia' }}</span>
                            </div>
                            @if ($order->tracking_number)
                                <div class="pt-2 border-t border-gray-200">
                                    <p class="text-gray-500 text-xs mb-2">
                                        Lacak resi ini langsung di website ekspedisi:
                                    </p>
                                    @php
                                        $trackingUrls = [
                                            'JNE' => 'https://www.jne.co.id/id/tracking/trace',
                                            'J&T' => 'https://www.jet.co.id/track',
                                            'SiCepat' => 'https://sicepat.com/checkAwb',
                                            'AnterAja' => 'https://anteraja.id/tracking',
                                            'Pos Indonesia' => 'https://www.posindonesia.co.id/id/tracking',
                                        ];
                                        $methodName = $order->shippingMethod->name;
                                        $trackingUrl = collect($trackingUrls)->first(
                                            fn($url, $key) => str_contains(strtolower($methodName), strtolower($key)),
                                        );
                                    @endphp
                                    @if ($trackingUrl)
                                        <a href="{{ $trackingUrl }}" target="_blank"
                                            class="inline-block text-xs border border-black px-3 py-1.5 hover:bg-black hover:text-white transition">
                                            Lacak di Website {{ $methodName }} →
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Produk yang Dipesan -->
                    <div class="space-y-2 text-sm">
                        @foreach ($order->items as $item)
                            <div class="flex justify-between">
                                <span>{{ $item->product_name }} @if ($item->size)
                                        ({{ $item->size }})
                                    @endif × {{ $item->quantity }}</span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="border-t border-gray-200 pt-2 flex justify-between font-medium">
                            <span>Total</span>
                            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Lacak Manual -->
                <div class="bg-gray-50 border border-gray-200 p-4 text-sm text-gray-600">
                    <p class="font-medium text-gray-800 mb-2">📦 Cara Melacak Pesanan Anda</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Salin nomor resi di atas</li>
                        <li>Kunjungi website resmi ekspedisi <strong>{{ $order->shippingMethod->name ?? '' }}</strong></li>
                        <li>Tempel nomor resi di kolom pencarian ekspedisi</li>
                        <li>Klik lacak untuk melihat posisi paket Anda</li>
                    </ol>
                </div>
            @else
                <!-- Order Tidak Ditemukan -->
                <div class="text-center py-10">
                    <p class="text-gray-500 mb-2">Order tidak ditemukan.</p>
                    <p class="text-sm text-gray-400">Pastikan nomor order yang Anda masukkan sudah benar.</p>
                </div>
            @endif
        @endif

    </div>
@endsection