<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = Order::whereNotNull('paid_at')->sum('total');
        $totalOrders = Order::count();
        $ordersThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $ordersPending = Order::where('status', 'pending')->count();
        $lowStock = Product::whereNotNull('stock')->where('stock', '<=', 5)->count();

        return [
            Stat::make('Revenue', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description('Total orders dibayar')
                ->icon('heroicon-m-currency-dollar'),

            Stat::make('Total Orders', $totalOrders)
                ->description('+'.$ordersThisMonth.' bulan ini')
                ->icon('heroicon-m-shopping-cart'),

            Stat::make('Orders Pending', $ordersPending)
                ->description('Menunggu pembayaran')
                ->color('amber')
                ->icon('heroicon-m-clock'),

            Stat::make('Low Stock', $lowStock)
                ->description('Stok kritis')
                ->color('rose')
                ->icon('heroicon-m-exclamation-triangle')
                ->url(ProductResource::getUrl('index'), false),
        ];
    }
}
