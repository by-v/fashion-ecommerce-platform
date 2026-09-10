@component('mail::message')
# Ada Pesanan Baru yang Perlu Diproses! 🎉

Pesanan **#{{ $order->order_number }}** telah berhasil dibayar dan menunggu untuk diproses.

@component('mail::panel')
**Total Pembayaran:** Rp {{ number_format($order->total, 0, ',', '.') }}
**Tanggal:** {{ $order->paid_at->format('d M Y, H:i') }} WIB
@endcomponent

---

### 📦 Detail Produk

@foreach($order->items as $item)
**{{ $item->product_name }}**@if($item->size) (Size: {{ $item->size }})@endif
Jumlah: {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }} = **Rp {{ number_format($item->subtotal, 0, ',', '.') }}**

@endforeach

**Subtotal:** Rp {{ number_format($order->subtotal, 0, ',', '.') }}
**Ongkos Kirim ({{ $order->shippingMethod->name }}):** Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
**Total:** Rp {{ number_format($order->total, 0, ',', '.') }}

---

### 📍 Informasi Pengiriman

**Penerima:** {{ $order->recipient_name }}
**No. HP:** {{ $order->phone }}
**Alamat:** {{ $order->address_detail }}
**Kota:** {{ $order->city }}
**Negara:** {{ $order->country }}
**Ekspedisi:** {{ $order->shippingMethod->name }} (Est. {{ $order->shippingMethod->estimated_days }} hari)

@component('mail::button', ['url' => config('app.url') . '/admin/orders'])
Kelola Pesanan di Admin Panel
@endcomponent

Segera proses pesanan ini!<br>
{{ config('app.name') }}
@endcomponent
