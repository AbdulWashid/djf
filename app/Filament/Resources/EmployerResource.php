<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployerResource\Pages;
use App\Models\Employer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

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
                Forms\Components\Section::make('Employer Details')
                    ->description('Fill out the details of the employer')
                    ->icon('heroicon-o-clipboard')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Name of the Employer')
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->helperText('Enter company details and description')
                            ->maxLength(5000)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status')
                            ->inline()
                            ->helperText('Employer visibility')
                            ->default(true)
                            ->required(),
                    ])
                    ->compact()
                    ->columns(2),

                Forms\Components\Section::make('Company Details')
                    ->description('Enter details about the company')
                    ->schema([
                        Forms\Components\TextInput::make('website')->url()->prefix('https://')->maxLength(255),
                        Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                        Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
                        Forms\Components\TextInput::make('address')->maxLength(255),
                        Forms\Components\TextInput::make('city')->maxLength(100),
                        Forms\Components\TextInput::make('state')->maxLength(100),

                        Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('postal_code')->maxLength(20),
                        FileUpload::make('logo')
                            ->image()
                            ->directory('employers')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/svg+xml',
                            ])
                            ->maxSize(2048)
                            ->imageEditor()
                            ->helperText('JPG, PNG, WEBP, SVG. Max 2 MB.'),
                    ])
                    ->compact()
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('logo'),

                TextColumn::make('website'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->badge()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->afterStateUpdated(function (Employer $record, bool $state) {
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