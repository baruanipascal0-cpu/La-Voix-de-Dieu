<?php

namespace App\Filament\Resources\Jurisdictions\Pages;

use App\Filament\Resources\Jurisdictions\JurisdictionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJurisdiction extends EditRecord
{
    protected static string $resource = JurisdictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
