@component('mail::message')
# Pembayaran Berhasil! 🎉

Halo, {{ $order->recipient_name }}

Pembayaran untuk pesanan **#{{ $order->order_number }}** telah kami terima. Pesanan Anda akan segera diproses untuk pengiriman.

@component('mail::panel')
**Total Dibayar:** Rp {{ number_format($order->total, 0, ',', '.') }}
@endcomponent

@component('mail::button', ['url' => route('order.confirmation', $order->order_number)])
Lihat Detail Pesanan
@endcomponent

Terima kasih telah berbelanja di {{ config('app.name') }}!
@endcomponent
