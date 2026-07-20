<?php

namespace App\Filament\Resources\ChurchInfos\Pages;

use App\Filament\Resources\ChurchInfos\ChurchInfoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChurchInfos extends ListRecords
{
    protected static string $resource = ChurchInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
