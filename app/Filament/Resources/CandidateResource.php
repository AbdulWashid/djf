<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Candidates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required(),

                Forms\Components\TextInput::make('last_name')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),

                Forms\Components\TextInput::make('phone'),

                Forms\Components\TextInput::make('nationality'),

                Forms\Components\Textarea::make('cover_letter')
                    ->columnSpanFull(),

                // Forms\Components\TextInput::make('resume_path')
                //     ->disabled(),

                Forms\Components\FileUpload::make('resume_path')
                    ->label('Resume')
                    ->directory('resumes')
                    ->downloadable()
                    ->openable(),

                Forms\Components\Toggle::make('status')
                    ->label('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Candidate')
                    ->getStateUsing(fn ($record) => $record->first_name.' '.$record->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nationality')
                    ->badge(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! is_null($record->email_verified_at)),

                Tables\Columns\ToggleColumn::make('status')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}
