<?php

namespace App\Filament\Resources\ChurchPhotos\Pages;

use App\Filament\Resources\ChurchPhotos\ChurchPhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChurchPhoto extends EditRecord
{
    protected static string $resource = ChurchPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
