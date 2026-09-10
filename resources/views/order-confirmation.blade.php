@extends('layouts.shop')

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div>
                    <p class="text-gray-500">No. Pesanan</p>
                    <p class="font-medium">#{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Status</p>
                    <p class="font-medium capitalize">{{ str_replace('_', ' ', $order->status) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Pesanan</p>
                    <p class="font-medium">{{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total Pembayaran</p>
                    <p class="font-medium">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-yellow-50 text-yellow-800 text-sm px-4 py-3">
                Selesaikan pembayaran sebelum {{ $order->payment_deadline->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide mb-3">Metode Pembayaran</h3>

            @if ($order->status === 'pending')
                <button id="payButton"
                    class="w-full bg-black text-white py-3 text-sm uppercase tracking-widest font-medium hover:bg-gray-800 transition">
                    Bayar Sekarang
                </button>
            @elseif($order->status === 'paid')
                <p class="text-sm text-green-600">✓ Pembayaran berhasil diterima.</p>
            @else
                <p class="text-sm text-gray-500">Status: {{ $order->status }}</p>
            @endif
        </div>

        <!-- Rincian Barang -->
        <div class="border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide mb-4">Rincian Barang</h3>
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <div>
                            <p>{{ $item->product_name }} @if ($item->size)
                                    ({{ $item->size }})
                                @endif
                            </p>
                            <p class="text-gray-500">{{ $item->quantity }}x Rp
                                {{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>
                        <p>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-200 mt-4 pt-4 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Ongkos Kirim</span>
                    <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Total</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Rincian Pesanan -->
        <div class="border border-gray-200 p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide mb-4">Rincian Pesanan</h3>
            <div class="text-sm space-y-1">
                <p><span class="text-gray-500">Metode Pengiriman:</span> {{ $order->shippingMethod->name }}</p>
                <p><span class="text-gray-500">Penerima:</span> {{ $order->recipient_name }} ({{ $order->phone }})</p>
                <p><span class="text-gray-500">Alamat:</span> {{ $order->address_detail }}, {{ $order->city }},
                    {{ $order->country }}</p>
            </div>
        </div>

    </div>

    @if ($order->status === 'pending')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            document.getElementById('payButton').addEventListener('click', function() {
                fetch("{{ route('payment.snapToken', $order->order_number) }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.snap_token) {
                            snap.pay(data.snap_token, {
                                onSuccess: function() {
                                    window.location.reload();
                                },
                                onPending: function() {
                                    window.location.reload();
                                },
                                onError: function() {
                                    alert('Pembayaran gagal, silakan coba lagi.');
                                },
                            });
                        } else {
                            alert(data.error ?? 'Gagal memuat pembayaran.');
                        }
                    });
            });
        </script>
    @endif
@endsection
