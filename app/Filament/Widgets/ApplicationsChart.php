<?php

namespace App\Filament\Widgets;

use App\Models\JobApplications;
use Filament\Widgets\ChartWidget;

class ApplicationsChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Applications (Last 30 Days)';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $labels[] = $date->format('d M');

            $data[] = JobApplications::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Applications',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}