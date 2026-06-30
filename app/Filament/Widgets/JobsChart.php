<?php

namespace App\Filament\Widgets;

use App\Models\Opening;
use Filament\Widgets\ChartWidget;

class JobsChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Jobs Posted (Last 30 Days)';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $labels[] = $date->format('d M');

            $data[] = Opening::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jobs',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

}