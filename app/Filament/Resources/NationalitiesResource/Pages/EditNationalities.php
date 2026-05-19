<?php

namespace App\Filament\Resources\NationalitiesResource\Pages;

use App\Filament\Resources\NationalitiesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNationalities extends EditRecord
{
    protected static string $resource = NationalitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
