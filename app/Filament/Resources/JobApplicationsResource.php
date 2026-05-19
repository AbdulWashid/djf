<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobApplicationsResource\Pages;
use App\Models\JobApplications;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Storage;

class JobApplicationsResource extends Resource
{
    protected static ?string $model = JobApplications::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Job Applications';
    protected static ?string $modelLabel = 'Job Application';
    protected static ?string $pluralModelLabel = 'Job Applications';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(12)->schema([
                Group::make()
                    ->columnSpan(8)
                    ->schema([
                        Section::make('Applicant Information')
                            ->description('Basic details submitted by the candidate.')
                            ->icon('heroicon-o-user')
                            ->columns(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(120),

                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(120),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(190),

                                TextInput::make('phone')
                                    ->required()
                                    ->maxLength(50),

                                TextInput::make('nationality')
                                    ->required()
                                    ->maxLength(120),

                                Select::make('opening_id')
                                    ->label('Job Opening')
                                    ->relationship('opening', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Section::make('Cover Letter')
                            ->description('Optional message from the applicant.')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Textarea::make('cover_letter')
                                    ->rows(8)
                                    ->maxLength(2000)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(4)
                    ->schema([
                        Section::make('Resume')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                TextInput::make('resume_path')
                                    ->label('Stored Resume Path')
                                    ->helperText('This is the stored file path (public disk).')
                                    ->disabled()
                                    ->dehydrated()
                                    ->formatStateUsing(fn (?string $state) => $state ?: '-'),

                                TextInput::make('resume_url')
                                    ->label('Resume URL')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($record) {
                                        if (!$record?->resume_path) {
                                            return '-';
                                        }

                                        return Storage::disk('public')->url($record->resume_path);
                                    }),
                            ]),

                        Section::make('Application Status')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Select::make('status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Pending',
                                        'reviewed' => 'Reviewed',
                                        'shortlisted' => 'Shortlisted',
                                        'rejected' => 'Rejected',
                                        'hired' => 'Hired',
                                    ])
                                    ->default('pending'),
                            ]),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('opening.title')
                    ->label('Job')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('first_name')
                    ->label('First')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Last')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->toggleable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nationality')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'reviewed' => 'info',
                        'shortlisted' => 'success',
                        'rejected' => 'danger',
                        'hired' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('resume_path')
                    ->label('Resume')
                    ->formatStateUsing(fn (?string $state) => $state ? basename($state) : '-')
                    ->url(fn ($record) => $record->resume_path ? Storage::disk('public')->url($record->resume_path) : null, true)
                    ->openUrlInNewTab()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'shortlisted' => 'Shortlisted',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                    ]),
                SelectFilter::make('opening_id')
                    ->label('Job')
                    ->relationship('opening', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),

                Tables\Actions\DeleteAction::make()
                    ->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplications::route('/create'),
            'view' => Pages\ViewJobApplications::route('/{record}'),
            'edit' => Pages\EditJobApplications::route('/{record}/edit'),
        ];
    }
}
