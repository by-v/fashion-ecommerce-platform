@extends('layouts.shop')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <h1 class="text-2xl font-bold mb-8">Checkout</h1>

        <form action="{{ route('checkout.store') }}" method="POST">
            @csrf
            
            <input type="hidden" name="submission_token" value="{{ session('checkout_submission_token') }}">

            <div class="grid md:grid-cols-2 gap-10">

                <!-- Left: Address Form -->
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-4">Informasi Penerima</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nama Penerima</label>
                            <input type="text" name="recipient_name" required
                                value="{{ old('recipient_name', Auth::user()->name) }}"
                                class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nomor HP</label>
                            <input type="text" name="phone" required value="{{ old('phone') }}"
                                class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Negara</label>
                                <input type="text" name="country" required value="{{ old('country', 'Indonesia') }}"
                                    class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Kota</label>
                                <input type="text" name="city" required value="{{ old('city') }}"
                                    class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Detail Alamat</label>
                            <textarea name="address_detail" required rows="3"
                                class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">{{ old('address_detail') }}</textarea>
                        </div>
                    </div>

                    <!-- Metode Pengiriman -->
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-4 mt-8">Metode Pengiriman</h2>
                    <div class="space-y-2">
                        @foreach ($shippingMethods as $method)
                            <label
                                class="flex items-center justify-between border border-gray-300 px-4 py-3 cursor-pointer has-[:checked]:border-black">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="shipping_method_id" value="{{ $method->id }}" required
                                        {{ $loop->first ? 'checked' : '' }} class="text-black focus:ring-black">
                                    <div>
                                        <p class="text-sm font-medium">{{ $method->name }}</p>
                                        <p class="text-xs text-gray-500">Estimasi {{ $method->estimated_days }} hari</p>
                                    </div>
                                </div>
                                <span class="text-sm">Rp {{ number_format($method->cost, 0, ',', '.') }}</span>
                            </label>
                        @endforeach
                    </div>

                    <!-- Metode Pembayaran (placeholder, akan diisi Tahap 5) -->
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-4 mt-8">Metode Pembayaran</h2>
                    <div class="border border-gray-300 px-4 py-3 text-sm text-gray-500">
                        QRIS / Virtual Account — dipilih otomatis saat pembayaran (via payment gateway)
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-4 border-b border-gray-200 pb-6 mb-6">
                        @foreach ($items as $item)
                            <div class="flex gap-4">
                                <div class="w-16 h-16 bg-gray-100 flex-shrink-0">
                                    @if ($item->product->image)
                                        <img src="{{ Storage::url($item->product->image) }}"
                                            class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ $item->product->name }}</p>
                                    @if ($item->variant)
                                        <p class="text-xs text-gray-500">Size: {{ $item->variant->size }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">{{ $item->quantity }}x Rp
                                        {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-sm">
                                    Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ongkos Kirim</span>
                            <span id="shippingCostDisplay">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold pt-3 border-t border-gray-200 mt-3">
                            <span>Total</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }} + Ongkir</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-black text-white py-3 text-sm uppercase tracking-widest font-medium hover:bg-gray-800 transition mt-8">
                        Lanjutkan Pembayaran
                    </button>
                </div>

            </div>
        </form>

    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
        });
    </script>
@endsection
