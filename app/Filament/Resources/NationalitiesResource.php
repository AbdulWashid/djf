<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NationalitiesResource\Pages;
use App\Models\Nationality;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NationalitiesResource extends Resource
{
    protected static ?string $model = Nationality::class;

    protected static ?string $slug = 'nationalities';

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form->schema([TextInput::make('name')->required(), TextInput::make('flag')->required(), Toggle::make('status')]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('flag'), ToggleColumn::make('status')->default(true)])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                // DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //DeleteBulkAction::make()->,
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNationalities::route('/'),
            //            'create' => Pages\CreateNationalities::route('/create'),
            //            'edit' => Pages\EditNationalities::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
