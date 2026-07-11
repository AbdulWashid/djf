<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Get;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $slug = 'faqs';
    protected static ?string $navigationGroup = 'Content';


    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('question')
                    ->suffix("?")
                    ->required(),

                RichEditor::make('answer')
                    ->required(),
                Select::make('section')
                    ->options([
                        'homepage' => 'Homepage',
                        'jobs' => 'Jobs',
                        'general' => 'General',
                    ])
                    ->live()
                    ->required(),
                Placeholder::make('job_faq_placeholder_note')
                    ->label('Job FAQ placeholders')
                    ->content('Available tokens: {category-name}, {place-name}. They will be replaced from the current job context.')
                    ->visible(fn (Get $get): bool => $get('section') === 'jobs'),
                Toggle::make('status')->default(true),

            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question'),
                TextColumn::make('section'),
                ToggleColumn::make('status'),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('section')
                    ->options([
                        'homepage' => 'Homepage',
                        'jobs' => 'Jobs',
                        'general' => 'General',

                    ]),
            ])
            ->actions([
                EditAction::make(),
                ReplicateAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort', 'Asc')
            ->reorderable('sort');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
