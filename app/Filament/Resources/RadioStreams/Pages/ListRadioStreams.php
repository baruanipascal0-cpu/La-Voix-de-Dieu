<?php

namespace App\Filament\Resources\RadioStreams\Pages;

use App\Filament\Resources\RadioStreams\RadioStreamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRadioStreams extends ListRecords
{
    protected static string $resource = RadioStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
