<?php

namespace App\Filament\Resources\Jurisdictions\Pages;

use App\Filament\Resources\Jurisdictions\JurisdictionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJurisdictions extends ListRecords
{
    protected static string $resource = JurisdictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
