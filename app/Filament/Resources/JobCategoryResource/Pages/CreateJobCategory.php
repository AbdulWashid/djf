<?php

namespace App\Filament\Resources\JobCategoryResource\Pages;

use App\Filament\Resources\JobCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJobCategory extends CreateRecord
{
    protected static string $resource = JobCategoryResource::class;

    public function afterCreate(): void
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
        ];
    }
}
