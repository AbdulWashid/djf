<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;

class CacheClear extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = 7;

    protected static string $view = 'filament.widgets.cache-clear';

    public function clearCacheAction(): Action
    {
        return Action::make('clearCache')
            ->label('Clear Cache')
            ->icon('heroicon-o-arrow-path')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                // Artisan::call('filament:cache-components');

                if (function_exists('opcache_reset')) {
                    opcache_reset();
                }

                Notification::make()
                    ->title('Cache cleared successfully!')
                    ->success()
                    ->send();
            });
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }
}