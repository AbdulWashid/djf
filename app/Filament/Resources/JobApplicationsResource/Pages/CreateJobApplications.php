<?php

namespace App\Filament\Resources\JobApplicationsResource\Pages;

use App\Filament\Resources\JobApplicationsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobApplications extends CreateRecord
{
    protected static string $resource = JobApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function hasCreateButton(): bool
    {
        return false;
    }
}
