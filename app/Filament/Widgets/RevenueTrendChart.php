<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Revenue 30 Hari Terakhir';

    protected function getData(): array
    {
        $revenue = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->whereNotNull('paid_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $labels = $revenue->pluck('date')->reverse()->values();
        $data = $revenue->pluck('total')->reverse()->values();

        return [
            'labels' => $labels->map(fn ($date) => Carbon::parse($date)->format('d M')),
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 85, 99, 0.1)',
                    'borderColor' => '#4B5563',
                    'tension' => 0.4,
                    'pointRadius' => 2,
                    'pointHoverRadius' => 4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
