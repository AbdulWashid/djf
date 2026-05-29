<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaticPageResource\Pages;
use App\Models\Opening;
use App\Models\StaticPage;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class StaticPageResource extends Resource
{
    protected static ?string $model = StaticPage::class;

    // nav group pages
    protected static ?string $navigationGroup = 'Content';
    // nav title

    protected static ?string $slug = 'static-pages';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->live(onBlur: true)
                ->required()
                ->afterStateUpdated(function (string $operation, $state, Get $get, Set $set) {
                    // Only auto-generate the slug on 'create' and if the user hasn't manually edited it.
                    if ($operation === 'create' && !$get('has_manual_slug_change')) {
                        $set('slug', self::generateUniqueSlug($state));
                    }
                }),
            TextInput::make('slug')
            ->unique(StaticPage::class, 'slug', fn($record) => $record),
            //                    ->afterStateUpdated(fn(Set $set) => $set('has_manual_slug_change', true)),
            //                Hidden::make('has_manual_slug_change')
            //                    ->default(false)
            //                    ->dehydrated(false),
            Section::make('Content')->schema([
                RichEditor::make('content')
                    ->label('')
                    ->required()
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h1',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ]),
            ]),

            Section::make('Seo Details')->schema([TextInput::make('meta_title')->required(), Textarea::make('meta_description'), TagsInput::make('meta_keywords')->separator(','), Textarea::make('twitter_tags')->rows(5), Textarea::make('og_tags')->label('Open Graph Tags')->rows(5)]),

            Section::make('Frequently asked questions')
                ->collapsible()
                ->description('Add frequently asked questions to this page')
                ->icon('heroicon-o-question-mark-circle')
                ->schema([
                    TextInput::make('faq_title')->label('FAQ Title'),
                    Repeater::make('faqs')
                        ->schema([
                            TextInput::make('question')->required()->maxLength(255)->placeholder('Enter question'),
                            RichEditor::make('answer')
                                ->required()
                                ->maxLength(65535)
                                ->placeholder('Enter answer')
                                ->toolbarButtons([
                                    'bold',
                                    'blockquote',
                                    'bulletList',
                                    'codeBlock',
                                    'h1',
                                    'h2',
                                    'h3',
                                    'italic',
                                    'link',
                                    'orderedList',
                                    'redo',
                                    'strike',
                                    'underline',
                                    'undo',
                                ]),
                        ])
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('Add FAQ')
                        ->columns(2),
                ]),

            Toggle::make('status')->default(true),
        ]);
    }
    protected static function generateUniqueSlug(?string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;
        while (StaticPage::where('slug', $slug)->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        return $slug;
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('slug')
                    //                    ->prefix('pages/')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status'),
            ])
            ->filters([TrashedFilter::make()])
            ->actions([
                Action::make('view_custom')
                    ->label('View')
                    ->icon('heroicon-o-link')
                    ->url(fn($record): string => route('page', ['slug' => $record->slug])) // Direct URL, opens in new tab (true)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make(), ForceDeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaticPages::route('/'),
            'create' => Pages\CreateStaticPage::route('/create'),
            'edit' => Pages\EditStaticPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }
}
