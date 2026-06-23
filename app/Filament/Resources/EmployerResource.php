<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployerResource\Pages;
use App\Models\Employer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EmployerResource extends Resource
{
    protected static ?string $model = Employer::class;

    protected static ?string $slug = 'employers';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static function getLastSortValue(): int
    {
        return Employer::max('id') ?? 0;
    }

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\Tabs::make('Banner Details')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Employer Details')
                                    ->description('Fill out the details of the employer')
                                    ->icon('heroicon-o-clipboard')
                                    ->schema([

                                        Forms\Components\TextInput::make('name')
                                            ->label('Name of the Employer')
                                            ->maxLength(255)
                                            ->columnSpan(2),
                                        Forms\Components\RichEditor::make('description')
                                            ->label('Description')
                                            ->helperText('Enter company details and description')
                                            ->maxLength(500)
                                            ->columnSpanFull(),

                                        // SpatieMediaLibraryFileUpload::make('image')
                                        //    ->collection('images') // Matches the collection name in your model
                                        //    ->label('Upload Image')
                                        //    ->image() // Optional: restricts to image files
                                        //    ->responsiveImages() // Optional: generates responsive images
                                        //    ->multiple(false), // Optional: allows multiple file uploads
                                        //     Add other options like ->downloadable(), ->previewable(), ->deletable()

                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Status')
                                            ->inline()
                                            ->helperText('Employer visibility')
                                            ->default(true),
                                    ])
                                    ->compact()
                                    ->columns(2),
                                Forms\Components\Section::make('Company Details')
                                    ->description('Enter details about the company')
                                    ->schema([
                                        Forms\Components\TextInput::make('website'),
                                        Forms\Components\TextInput::make('email'),
                                        Forms\Components\TextInput::make('phone'),
                                        Forms\Components\TextInput::make('address'),
                                        Forms\Components\TextInput::make('city'),
                                        Forms\Components\TextInput::make('state'),
                                        Forms\Components\TextInput::make('country'),
                                        Forms\Components\TextInput::make('postal_code'),
                                    ])
                                    ->compact()
                                    ->columns(2),

                            ]),
                        Forms\Components\Tabs\Tab::make('Company Logo')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('Logo')
                                    ->label('Company Logo')
                                    ->description('Upload company logo here')
                                    ->schema([
                                        FileUpload::make('logo')
                                           ->image()
                                            ->imagePreviewHeight('250')
                                            ->panelLayout('compact')
                                            ->imageResizeMode('cover')
                                            ->imageResizeTargetWidth('1200')
                                            ->imageResizeTargetHeight('800')
                                            ->acceptedFileTypes(['image/*'])
                                            ->helperText('Upload a company logo. Recommended size: 1200x800px')
                                            ->columnSpanFull(),
                                    ])
                                    ->compact(),
                            ]),

                    ])
                    ->columnSpanFull(),
            ]);

        // return $form
        //    ->schema([
        //        TextInput::make('name')
        //            ->required(),
        //        TextInput::make('logo'),
        //        TextInput::make('description'),
        //        TextInput::make('website'),
        //        TextInput::make('email'),
        //        TextInput::make('phone'),
        //        TextInput::make('address'),
        //        TextInput::make('city'),
        //        TextInput::make('state'),
        //        TextInput::make('country'),
        //        TextInput::make('postal_code'),
        //        Checkbox::make('is_active'),
        //        Placeholder::make('created_at')
        //            ->label('Created Date')
        //            ->content(fn(?Employer $record): string => $record?->created_at?->diffForHumans() ?? '-'),
        //        Placeholder::make('updated_at')
        //            ->label('Last Modified Date')
        //            ->content(fn(?Employer $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
        //    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('logo'),
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('images')
                //    ->conversion('thumb')
                    ->circular()
                    ->label('Featured Image'),


                TextColumn::make('website'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->afterStateUpdated(function (Employer $record, bool $state) {
                        // send notification
                        Notification::make()
                            ->title('Saved successfully')
                            ->success()
                            ->send();

                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployers::route('/'),
            'create' => Pages\CreateEmployer::route('/create'),
            'edit' => Pages\EditEmployer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
