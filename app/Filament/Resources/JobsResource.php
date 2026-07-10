<?php

namespace App\Filament\Resources;

use App\Enums\EmploymentType;
use App\Filament\Resources\JobsResource\Pages;
use App\Models\Nationality;
use App\Models\Opening;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
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
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobsResource extends Resource
{
    protected static ?string $model = Opening::class;
    protected static ?string $navigationLabel = 'Job Postings';
    protected static ?string $navigationGroup = 'Jobs';

    protected static ?string $pluralModelLabel = 'Job Postings';
    protected static ?string $modelLabel = 'Job Posting';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'jobs';

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Job Details')
                ->description('Fill out the details of the job')
                ->icon('heroicon-o-clipboard')
                ->schema([
                    TextInput::make('title')
                        ->live(onBlur: true)
                        ->required()
                        ->afterStateUpdated(function (string $operation, $state, Get $get, Set $set) {
                            // Only auto-generate the slug on 'create' and if the user hasn't manually edited it.
                            //                                if ($operation === 'create') {
                            $set('slug', self::generateUniqueSlug($state));
                            //                                }
                        }),
                    Select::make('employer_id')->relationship(name: 'employer', titleAttribute: 'name', modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true))->preload()->searchable()->required(),
                    Select::make('job_category_id')->relationship('job_category', titleAttribute: 'name', modifyQueryUsing: fn(Builder $query) => $query->where('status', true))->preload()//                            ->searchable()
                    ->required(),
                    TextInput::make('slug')->unique(ignoreRecord: true)->afterStateUpdated(fn(Set $set) => $set('has_manual_slug_change', true)),
                    Hidden::make('has_manual_slug_change')->default(false)->dehydrated(false),
                ])
                ->columns(2),
            Section::make('Job Description')->schema([
                RichEditor::make('description')
                    ->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h1', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo'])
                    ->required(),
                RichEditor::make('responsibilities')->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h1', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo']),
                RichEditor::make('skills')->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h1', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo']),
                RichEditor::make('benefits')->toolbarButtons(['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h1', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo']),
            ]),
            Section::make('Additional Information')
                ->schema([
                    Select::make('job_type')->label('Employment Type')->options(EmploymentType::class)->required(),
                    // TextInput::make('location')->required(),
                    Select::make('location_id')
                        ->label('Location')
                        ->relationship('location', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->required()
                                ->unique(ignoreRecord: true),
                        ]),
                    TextInput::make('salary_range')->suffix('AED/Year')->required(),
                    Select::make('expected_nationalities')
                        ->multiple()
                        ->preload()
                        ->label('Expected Nationalities')
                        ->required()
                        ->options(Nationality::active()->pluck('name', 'flag')->toArray())
                        ->native(false),
                    Select::make('gender')
                        ->options(['Male' => 'Male', 'Female' => 'Female', 'Both (Male/Female)' => 'Both (Male/Female)', 'Other' => 'Other'])
                        ->required(),
                    TextInput::make('required_experience')->suffix('Years')->required(),
                    Toggle::make('status')->default(true),
                    Toggle::make('featured'),
                ])
                ->columns(2),
            Section::make('Seo Details')->schema([
                TextInput::make('meta_title')->required(),
                Textarea::make('meta_description'),
                TagsInput::make('meta_keywords')->separator(','),
                Textarea::make('twitter_tags')->rows(5),
                Textarea::make('og_tags')->label('Open Graph Tags')->rows(5),
                //                        RichEditor::make('benefits'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('title')->searchable()->sortable(), 
                    TextColumn::make('employer.name')->searchable()->sortable(), 
                    TextColumn::make('job_type'), 
                    // TextColumn::make('location'), 
                    TextColumn::make('location.name')
                        ->label('Location')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('salary_range'), 
                    TextColumn::make('gender'), 
                    ToggleColumn::make('featured')->label('Featured')->onColor('success'), 
                    ToggleColumn::make('status')->label('Status')->onColor('success')->offColor('danger')]
                )
            ->filters([
                //
            ])
            ->actions([EditAction::make(), DeleteAction::make()->requiresConfirmation()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()->requiresConfirmation()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJobs::route('/create'),
            'edit' => Pages\EditJobs::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['employer']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'employer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];
        if ($record->employer) {
            $details['Employer'] = $record->employer->name;
        }
        return $details;
    }

    protected static function generateUniqueSlug(?string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;
        while (Opening::where('slug', $slug)->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        return $slug;
    }
}
