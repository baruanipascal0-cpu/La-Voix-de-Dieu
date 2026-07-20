<?php

namespace App\Filament\Resources\RadioStreams\Pages;

use App\Filament\Resources\RadioStreams\RadioStreamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRadioStream extends EditRecord
{
    protected static string $resource = RadioStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
