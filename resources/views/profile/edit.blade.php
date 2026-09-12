@extends('layouts.shop')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-1/4">
            <div class="bg-white border border-gray-100 rounded-lg p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 bg-black text-white rounded-full flex items-center justify-center text-xl font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="font-bold text-lg leading-tight">{{ Auth::user()->name }}</h2>
                        <p class="text-sm text-gray-500 uppercase tracking-tighter">{{ Auth::user()->role }}</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <a href="#profile" class="flex items-center px-4 py-3 text-sm font-medium text-black bg-gray-50 rounded-md transition" id="nav-profile">
                        Profil Saya
                    </a>
                    <a href="#orders" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-black hover:bg-gray-50 rounded-md transition" id="nav-orders">
                        Riwayat Pesanan
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition">
                            Keluar
                        </button>
                    </form>
                </nav>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 space-y-8">
            
            <!-- Profile Section -->
            <div id="section-profile" class="space-y-6">
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold uppercase tracking-widest text-sm">Informasi Profil</h3>
                    </div>
                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold uppercase tracking-widest text-sm">Ubah Password</h3>
                    </div>
                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden border-red-100">
                    <div class="px-6 py-4 border-b border-gray-100 bg-red-50/30">
                        <h3 class="font-bold uppercase tracking-widest text-sm text-red-800">Hapus Akun</h3>
                    </div>
                    <div class="p-6">
                        <div class="max-w-xl">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div id="section-orders" class="hidden space-y-6">
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold uppercase tracking-widest text-sm">Pesanan Saya</h3>
                    </div>
                    <div class="p-6">
                        @if($orders->isEmpty())
                            <div class="text-center py-12">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <p class="text-gray-500">Anda belum memiliki pesanan.</p>
                                <a href="{{ route('shop') }}" class="inline-block mt-4 text-sm font-bold uppercase underline tracking-widest">Mulai Belanja</a>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach($orders as $order)
                                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-3 flex justify-between items-center border-b border-gray-100 flex-wrap gap-2">
                                            <div class="flex gap-4 text-xs font-bold uppercase tracking-tighter">
                                                <span>Order #{{ $order->order_number }}</span>
                                                <span class="text-gray-400">|</span>
                                                <span class="text-gray-500">{{ $order->created_at->format('d M Y') }}</span>
                                            </div>
                                            <div class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest 
                                                @if($order->status === 'pending') bg-amber-100 text-amber-700
                                                @elseif($order->status === 'paid') bg-green-100 text-green-700
                                                @elseif($order->status === 'shipped') bg-blue-100 text-blue-700
                                                @elseif($order->status === 'completed') bg-gray-100 text-gray-700
                                                @else bg-red-100 text-red-700 @endif">
                                                {{ $order->status }}
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            @foreach($order->items as $item)
                                                <div class="flex items-center gap-4 py-2 @if(!$loop->last) border-b border-gray-50 @endif">
                                                    @if($item->product->image)
                                                        <img src="{{ Storage::url($item->product->image) }}" class="h-16 w-16 object-cover rounded shadow-sm">
                                                    @else
                                                        <div class="h-16 w-16 bg-gray-100 flex items-center justify-center text-[10px] text-gray-400">No Image</div>
                                                    @endif
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-bold truncate">{{ $item->product->name }}</p>
                                                        <p class="text-xs text-gray-500">x{{ $item->quantity }} • {{ $item->size ?? 'Default' }}</p>
                                                    </div>
                                                    <div class="text-sm font-bold">
                                                        Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                                <div class="text-xs text-gray-500">
                                                    Pengiriman: {{ $order->shippingMethod->name }} (Rp {{ number_format($order->shipping_cost, 0, ',', '.') }})
                                                </div>
                                                <div class="text-sm font-bold">
                                                    Total: <span class="text-lg">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            @if($order->status === 'pending')
                                                <div class="mt-4">
                                                    <a href="{{ route('order.confirmation', $order->order_number) }}" class="inline-block w-full text-center py-2 bg-black text-white text-xs font-bold uppercase tracking-widest hover:bg-gray-800 transition">
                                                        Lanjutkan Pembayaran
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navProfile = document.getElementById('nav-profile');
        const navOrders = document.getElementById('nav-orders');
        const sectionProfile = document.getElementById('section-profile');
        const sectionOrders = document.getElementById('section-orders');

        function showSection(section) {
            if (section === 'profile') {
                sectionProfile.classList.remove('hidden');
                sectionOrders.classList.add('hidden');
                navProfile.classList.add('bg-gray-50', 'text-black');
                navProfile.classList.remove('text-gray-600');
                navOrders.classList.remove('bg-gray-50', 'text-black');
                navOrders.classList.add('text-gray-600');
            } else {
                sectionProfile.classList.add('hidden');
                sectionOrders.classList.remove('hidden');
                navOrders.classList.add('bg-gray-50', 'text-black');
                navOrders.classList.remove('text-gray-600');
                navProfile.classList.remove('bg-gray-50', 'text-black');
                navProfile.classList.add('text-gray-600');
            }
        }

        navProfile.addEventListener('click', (e) => {
            e.preventDefault();
            showSection('profile');
            history.pushState(null, null, '#profile');
        });

        navOrders.addEventListener('click', (e) => {
            e.preventDefault();
            showSection('orders');
            history.pushState(null, null, '#orders');
        });

        // Handle hash on load
        if (window.location.hash === '#orders') {
            showSection('orders');
        }
    });
</script>
@endsection
