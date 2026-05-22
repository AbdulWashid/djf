<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageMetaResource\Pages;
use App\Models\HomePageMeta;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomePageMetaResource extends Resource
{
    protected static ?string $model = HomePageMeta::class;

    protected static ?string $slug = 'home-page-meta';

    protected static ?string $navigationGroup = 'Sites';

    protected static ?string $navigationLabel = 'Home Page Meta';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meta_title')
                    ->label('Meta Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meta_description')
                    ->label('Meta Description')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePageMetas::route('/'),
            'create' => Pages\CreateHomePageMeta::route('/create'),
            'edit' => Pages\EditHomePageMeta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
