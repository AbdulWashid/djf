<?php

namespace App\Filament\Resources\NationalitiesResource\Pages;

use App\Filament\Resources\NationalitiesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNationalities extends CreateRecord
{
    protected static string $resource = NationalitiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    // return to index
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
