<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fashion Store</title>
    @vite(['resources/css/admin-demo.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#FAFAFA] dark:bg-[#0D0D0D] text-[#09090B] dark:text-[#FAFAFA] min-h-screen font-sans transition-colors duration-200">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden lg:flex flex-col w-64 bg-[#FFFFFF] dark:bg-[#18181B] border-r border-[#E4E4E7] dark:border-[#3F3F46]">
            <div class="p-6">
                <h1 class="font-montserrat text-xl font-bold tracking-widest uppercase">Fashion Store</h1>
            </div>
            <nav class="flex-1 px-4 space-y-1 overflow-y-auto">
                @foreach ([
                    ['icon' => 'heroicon-m-home', 'label' => 'Dashboard', 'active' => true],
                    ['icon' => 'heroicon-m-shopping-bag', 'label' => 'Orders', 'active' => false],
                ] as $item)
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ $item['active'] ? 'bg-[#F5F5F5] dark:bg-[#27272A]' : 'hover:bg-[#F5F5F5] dark:hover:bg-[#27272A]' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#111111] dark:text-[#FFFFFF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="ml-3 text-sm font-medium text-[#09090B] dark:text-[#FAFAFA]">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="p-4 border-t border-[#E4E4E7] dark:border-[#3F3F46]">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-[#F5F5F5] dark:bg-[#27272A] flex items-center justify-center">
                        <span class="font-montserrat text-sm font-bold text-[#111111] dark:text-[#FFFFFF]">AD</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium font-montserrat text-[#09090B] dark:text-[#FAFAFA]">Admin</p>
                        <p class="text-xs text-[#475569] dark:text-[#94A3B8]">admin@fashionstore.com</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-[#FFFFFF] dark:bg-[#18181B] border-b border-[#E4E4E7] dark:border-[#3F3F46] px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4 lg:hidden">
                    <button class="text-[#09090B] dark:text-[#FAFAFA]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-md mx-4">
                    <div class="relative">
                        <input type="text" placeholder="Search..." class="w-full border border-[#E4E4E7] dark:border-[#3F3F46] rounded-lg py-2 px-4 text-sm bg-[#F5F5F5] dark:bg-[#27272A] focus:outline-none focus:ring-1 focus:ring-[#111111] dark:focus:ring-[#FFFFFF]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-1/2 -translate-y-1/2 text-[#475569] dark:text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Right Header -->
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button x-data="{ dark: $persist(false).as('darkMode') }" x-init="$watch('dark', val => $root.classList.toggle('dark', val))" @click="dark = !dark" class="p-2 rounded-lg hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#111111] dark:text-[#FFFFFF]" x-show="!dark" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#111111] dark:text-[#FFFFFF]" x-show="dark" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <button class="p-2 rounded-lg hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#111111] dark:text-[#FFFFFF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-[#DC2626]"></span>
                    </button>
                </div>
            </header>

            <!-- Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Summary Numbers -->
                <div class="mb-6">
                    <h2 class="font-montserrat text-2xl font-bold text-[#09090B] dark:text-[#FAFAFA] mb-1">Dashboard</h2>
                    <p class="text-[#475569] dark:text-[#94A3B8] text-sm">Ringkasan penjualan dan pesanan hari ini</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    @foreach ([
                        ['label' => 'Revenue Hari Ini', 'value' => 'Rp 4.250.000', 'trend' => 12.5],
                        ['label' => 'Total Pesanan', 'value' => '128', 'trend' => -2.4],
                        ['label' => 'Pending', 'value' => '23', 'trend' => 5.7],
                        ['label' => 'Stok Rendah', 'value' => '8', 'trend' => 0],
                    ] as $stat)
                        <div class="bg-[#FFFFFF] dark:bg-[#18181B] rounded-xl p-5 border border-[#E4E4E7] dark:border-[#3F3F46] shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm text-[#475569] dark:text-[#94A3B8] mb-1">{{ $stat['label'] }}</p>
                                    <h3 class="font-montserrat text-2xl font-bold text-[#09090B] dark:text-[#FAFAFA]">{{ $stat['value'] }}</h3>
                                </div>
                                <div class="p-2 bg-[#F5F5F5] dark:bg-[#27272A] rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#111111] dark:text-[#FFFFFF]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center">
                                @if($stat['trend'] > 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#16A34A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    <span class="ml-1 text-sm text-[#16A34A] font-medium">+{{ $stat['trend'] }}%</span>
                                @elseif($stat['trend'] < 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#DC2626]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                    </svg>
                                    <span class="ml-1 text-sm text-[#DC2626] font-medium">{{ $stat['trend'] }}%</span>
                                @else
                                    <span class="ml-1 text-sm text-[#475569] dark:text-[#94A3B8] font-medium">-</span>
                                @endif
                                <span class="ml-2 text-xs text-[#475569] dark:text-[#94A3B8]">dari kemarin</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Chart Section -->
                    <div class="lg:col-span-2">
                        <div class="bg-[#FFFFFF] dark:bg-[#18181B] rounded-xl border border-[#E4E4E7] dark:border-[#3F3F46] shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="font-montserrat text-lg font-semibold text-[#09090B] dark:text-[#FAFAFA]">Revenue 30 Hari Terakhir</h3>
                                    <p class="text-sm text-[#475569] dark:text-[#94A3B8]">Januari 1 - 30, 2026</p>
                                </div>
                            </div>

                            <!-- CSS Bar Chart -->
                            <div class="h-64 flex items-end justify-between gap-2">
                                @for ($i = 0; $i < 30; $i++)
                                    @php
                                        $height = rand(20, 90);
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center group relative">
                                        <div class="w-full bg-[#F5F5F5] dark:bg-[#27272A] rounded-t-sm relative overflow-hidden">
                                            <div class="absolute bottom-0 left-0 right-0 bg-[#111111] dark:bg-[#FFFFFF] rounded-t-sm transition-all duration-500" style="height: {{ $height }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-[#475569] dark:text-[#94A3B8] mt-2">
                                            @php echo date('d', strtotime("-$i days")) @endphp
                                        </span>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel -->
                    <div class="space-y-6">
                        <!-- Mini Calendar -->
                        <div class="bg-[#FFFFFF] dark:bg-[#18181B] rounded-xl border border-[#E4E4E7] dark:border-[#3F3F46] shadow-sm p-6">
                            <h3 class="font-montserrat text-lg font-semibold text-[#09090B] dark:text-[#FAFAFA] mb-4">Kalender</h3>
                            <div class="text-center">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="font-montserrat font-bold text-[#09090B] dark:text-[#FAFAFA]">September 2026</span>
                                    <div class="flex gap-1">
                                        <button class="p-1 hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] rounded text-[#111111] dark:text-[#FFFFFF]"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" /></svg></button>
                                        <button class="p-1 hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] rounded text-[#111111] dark:text-[#FFFFFF]"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" /></svg></button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-7 gap-1 text-center">
                                    @foreach(['M','S','R','K','J','S','M'] as $d)
                                        <div class="text-xs text-[#475569] dark:text-[#94A3B8] font-medium">{{ $d }}</div>
                                    @endforeach
                                    @for($i=1; $i<=30; $i++)
                                        <div class="aspect-square flex items-center justify-center rounded text-sm cursor-pointer hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] text-[#09090B] dark:text-[#FAFAFA]">
                                            {{ $i }}
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- Donut Progress -->
                        <div class="bg-[#FFFFFF] dark:bg-[#18181B] rounded-xl border border-[#E4E4E7] dark:border-[#3F3F46] shadow-sm p-6">
                            <h3 class="font-montserrat text-lg font-semibold text-[#09090B] dark:text-[#FAFAFA] mb-4">Target Bulanan</h3>
                            <div class="relative w-32 h-32 mx-auto">
                                <svg class="w-full h-full" viewBox="0 0 36 36">
                                    <path class="text-[#F5F5F5] dark:text-[#27272A]" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                    <path class="text-[#111111] dark:text-[#FFFFFF]" stroke-dasharray="75, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                                </svg>
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                                    <span class="font-montserrat text-xl font-bold text-[#09090B] dark:text-[#FAFAFA]">75%</span>
                                </div>
                            </div>
                            <p class="text-center text-sm text-[#475569] dark:text-[#94A3B8] mt-2">Rp 750 juta / Rp 1 miliar</p>
                        </div>
                    </div>
                </div>

                <!-- Latest Orders Table -->
                <div class="bg-[#FFFFFF] dark:bg-[#18181B] rounded-xl border border-[#E4E4E7] dark:border-[#3F3F46] shadow-sm p-6 mt-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-montserrat text-lg font-semibold text-[#09090B] dark:text-[#FAFAFA]">Pesanan Terbaru</h3>
                        <a href="#" class="text-sm font-medium text-[#111111] dark:text-[#FFFFFF] hover:underline">Lihat Semua</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-[#E4E4E7] dark:border-[#3F3F46]">
                                    <th class="text-left py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">No. Order</th>
                                    <th class="text-left py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">Pelanggan</th>
                                    <th class="text-left py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">Total</th>
                                    <th class="text-left py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">Status</th>
                                    <th class="text-left py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E4E4E7] dark:divide-[#3F3F46]">
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $statuses = ['pending' => 'amber', 'paid' => 'green', 'shipped' => 'blue', 'completed' => 'emerald', 'cancelled' => 'red'];
                                        $status = array_rand($statuses);
                                    @endphp
                                    <tr class="hover:bg-[#F5F5F5] dark:hover:bg-[#27272A] transition-colors">
                                        <td class="py-3 px-4 text-sm text-[#09090B] dark:text-[#FAFAFA] font-medium">ORD-2026-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td class="py-3 px-4 text-sm text-[#09090B] dark:text-[#FAFAFA]">User {{ $i }}</td>
                                        <td class="py-3 px-4 text-sm text-[#09090B] dark:text-[#FAFAFA]">Rp {{ rand(500000, 5000000) }}</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statuses[$status] }}-100 text-{{ $statuses[$status] }}-800 dark:bg-{{ $statuses[$status] }}-900 dark:text-{{ $statuses[$status] }}-100">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-sm text-[#475569] dark:text-[#94A3B8]">{{ date('d M Y', strtotime("-$i days")) }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dark mode persistence
            const savedTheme = localStorage.getItem('darkMode') || 'false';
            if (savedTheme === 'true') {
                document.documentElement.classList.add('dark');
            }

            // Toggle dark mode
            document.querySelector('[x-data]').addEventListener('click', function () {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark);
            });
        });
    </script>
</body>
</html>
