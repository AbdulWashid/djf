<?php

namespace App\Filament\Pages;

use App\Models\HomePageMeta as HomePageMetaModel;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomePageMeta extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home Page Meta';

    protected static ?string $navigationGroup = 'Sites';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.home-page-meta';

    public ?array $data = [];

    public function mount(): void
    {
        $content = HomePageMetaModel::firstOrCreate([]);

        $this->form->fill($content->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Home Page Meta Tags')
                    ->description('Manage the metadata shown on the homepage.')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(4),
                        TagsInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->separator(','),
                        Textarea::make('twitter_tags')
                            ->label('Twitter Tag')
                            ->rows(6),
                        Textarea::make('og_tags')
                            ->label('Open Graph Tag')
                            ->rows(6),
                    ])
                    ->columns(1),
            ]);
    }

    public function save(): void
    {
        $content = HomePageMetaModel::firstOrCreate([]);

        $content->update($this->form->getState());

        Notification::make()
            ->title('Saved successfully.')
            ->success()
            ->send();
    }
    public static function canAccess(): bool
    {
        return auth()->user()->can('view_home_page_meta');
    }
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}