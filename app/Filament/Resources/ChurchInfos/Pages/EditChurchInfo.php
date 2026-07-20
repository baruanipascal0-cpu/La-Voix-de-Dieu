<?php

namespace App\Filament\Resources\ChurchInfos\Pages;

use App\Filament\Resources\ChurchInfos\ChurchInfoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChurchInfo extends EditRecord
{
    protected static string $resource = ChurchInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
