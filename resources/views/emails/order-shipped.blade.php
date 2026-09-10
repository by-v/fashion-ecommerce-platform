@component('mail::message')
# Pesanan Anda Sedang Dalam Perjalanan! 🚚

Halo, {{ $order->recipient_name }}

Pesanan **#{{ $order->order_number }}** telah dikirim oleh {{ config('app.name') }}.

@component('mail::panel')
**Ekspedisi:** {{ $order->shippingMethod->name }}
**Nomor Resi:** {{ $order->tracking_number ?? 'Akan segera diupdate' }}
**Estimasi Tiba:** {{ $order->shippingMethod->estimated_days }} hari kerja
@endcomponent

### Cara Melacak Pesanan Anda

1. Kunjungi halaman **Lacak Pesanan** di website kami
2. Masukkan **Nomor Order** Anda: `#{{ $order->order_number }}`
3. Nomor resi di atas juga bisa dilacak langsung di website resmi **{{ $order->shippingMethod->name }}**

@component('mail::button', ['url' => config('app.url') . '/track-order'])
Lacak Pesanan Sekarang
@endcomponent

---

### Detail Produk

@foreach ($order->items as $item)
**{{ $item->product_name }}**@if ($item->size) ({{ $item->size }})@endif — {{ $item->quantity }} pcs

@endforeach

**Alamat Pengiriman:**
{{ $order->address_detail }}, {{ $order->city }}, {{ $order->country }}

Terima kasih telah berbelanja di {{ config('app.name') }}!
@endcomponent
