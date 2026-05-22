<?php

namespace App\Filament\Resources\HomePageMetaResource\Pages;

use App\Filament\Resources\HomePageMetaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomePageMeta extends EditRecord
{
    protected static string $resource = HomePageMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
