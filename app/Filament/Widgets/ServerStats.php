<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ServerStats extends BaseWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        return [
            Stat::make('PHP', PHP_VERSION)
                ->description('Current PHP Version')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('success'),

            Stat::make('Laravel', app()->version())
                ->description('Framework Version')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Environment', ucfirst(app()->environment()))
                ->description(config('app.debug') ? 'Debug Enabled' : 'Debug Disabled')
                ->descriptionIcon(
                    config('app.debug')
                        ? 'heroicon-m-exclamation-triangle'
                        : 'heroicon-m-shield-check'
                )
                ->color(config('app.debug') ? 'warning' : 'success'),

            Stat::make('Database', DB::connection()->getDatabaseName())
                ->description(DB::connection()->getDriverName())
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('info'),

            Stat::make('Memory Limit', ini_get('memory_limit'))
                ->description('PHP Memory')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('gray'),

            Stat::make('Upload Limit', ini_get('upload_max_filesize'))
                ->description('Max Upload Size')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('warning'),

            Stat::make('Timezone', config('app.timezone'))
                ->description(now()->format('d M Y H:i'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make('Server', php_uname('n'))
                ->description(PHP_OS_FAMILY)
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),
        ];
    }
}