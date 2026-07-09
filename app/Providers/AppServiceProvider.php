<?php

namespace App\Providers;

use Filament\Tables\Table;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use App\Models\Blog\Post;
use App\Observers\PostObserver;
use App\Settings\MailSettings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);

        Table::configureUsing(function (Table $table): void {
            $table
                ->emptyStateHeading('No data yet')
                ->defaultPaginationPageOption(10)
                ->paginated([10, 25, 50, 100])
                ->extremePaginationLinks()
                ->defaultSort('created_at', 'desc');
        });


        // # \Opcodes\LogViewer
        LogViewer::auth(function ($request) {
            $user = auth()->user();

            return $user?->can('access_log_viewer')
                || $user?->hasRole(config('filament-shield.super_admin.name'));
        });

        // # Filament Hooks
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn(): View => view('filament.components.panel-footer'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): View => view('filament.components.button-website'),
        );
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn() => view('filament.components.impersonate-banner')
        );

        if (app()->bound(MailSettings::class)) {
            try {
                $mailSettings = MailSettings::safe();
                $mailSettings->loadMailSettingsToConfig();
            } catch (\Throwable $e) {
                try {
                    (new MailSettings(MailSettings::defaults()))->loadMailSettingsToConfig();
                } catch (\Throwable $ignored) {
                    // ignore if settings table isn't ready or stored values are unreadable
                }
            }
        }
    }
}
