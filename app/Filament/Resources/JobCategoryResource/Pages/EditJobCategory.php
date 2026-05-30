<?php

namespace App\Filament\Resources\JobCategoryResource\Pages;

use App\Filament\Resources\JobCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditJobCategory extends EditRecord
{
    protected static string $resource = JobCategoryResource::class;

    public function afterSave(): void
    {
        $this->syncLogoColumn();
    }

    private function syncLogoColumn(): void
    {
        $this->record->refresh();

        $media = $this->record->getFirstMedia('job-categories');
        
        if ($media) {
            $relativePath = $media->getPath();
            \Log::info('JobCategory logo sync', ['id' => $this->record->id, 'media_path' => $relativePath]);
            $this->record->update(['logo' => $relativePath]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
