<?php

namespace App\Filament\Resources\NationalitiesResource\Pages;

use App\Filament\Resources\NationalitiesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNationalities extends ListRecords
{
    protected static string $resource = NationalitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
