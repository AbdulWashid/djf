<?php

namespace App\Filament\Resources\JobsResource\Pages;

use App\Filament\Resources\JobsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobs extends CreateRecord
{
    protected static string $resource = JobsResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
