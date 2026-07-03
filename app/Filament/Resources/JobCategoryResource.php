<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobCategoryResource\Pages;
use App\Models\JobCategory;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class JobCategoryResource extends Resource
{
    protected static ?string $model = JobCategory::class;

    protected static ?string $slug = 'job-categories';
    protected static ?string $navigationGroup = 'Jobs';
    protected static ?string $navigationLabel = 'Job Categories';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, Get $get, Set $set) {
                    // Only auto-generate the slug on 'create' and if the user hasn't manually edited it.
                    $set('slug', Str::slug($state));
                })
                ->required(),

            // Slug
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('position')
                ->label('Position')
                ->numeric()
                ->required()
                ->default(1),

            SpatieMediaLibraryFileUpload::make('logo')
                ->label('Logo')
                ->collection('job-categories')
                ->image()
                ->imagePreviewHeight('150')
                ->helperText('Upload category logo image.'),

            Toggle::make('status')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('position')->label('Position')->sortable(),
                ToggleColumn::make('status'),
            ])
            ->filters([TrashedFilter::make()])
            ->actions([EditAction::make(),DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([])]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobCategories::route('/'),
            //'create' => Pages\CreateJobCategory::route('/create'),
            //'edit' => Pages\EditJobCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
