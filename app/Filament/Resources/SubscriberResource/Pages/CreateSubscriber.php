<?php

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriber extends CreateRecord
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    // return to index
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
