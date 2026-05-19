<?php

namespace App\Filament\Resources\JobApplicationsResource\Pages;

use App\Filament\Resources\JobApplicationsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditJobApplications extends EditRecord
{
    protected static string $resource = JobApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
