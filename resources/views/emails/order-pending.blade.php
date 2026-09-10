@component('mail::message')
# Halo, {{ $order->recipient_name }}

Kami telah menerima pesanan Anda **#{{ $order->order_number }}** dan menunggu pembayaran.

@component('mail::panel')
**Total Pembayaran:** Rp {{ number_format($order->total, 0, ',', '.') }}
@endcomponent

@component('mail::button', ['url' => route('order.confirmation', $order->order_number)])
Cek Cara Membayar
@endcomponent

Selesaikan pembayaran sebelum **{{ $order->payment_deadline->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB**.

---

### Rincian Pesanan

@foreach ($order->items as $item)
**{{ $item->product_name }}**@if ($item->size) ({{ $item->size }})@endif
{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }} = Rp {{ number_format($item->subtotal, 0, ',', '.') }}

@endforeach

**Subtotal:** Rp {{ number_format($order->subtotal, 0, ',', '.') }}
**Ongkos Kirim:** Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
    