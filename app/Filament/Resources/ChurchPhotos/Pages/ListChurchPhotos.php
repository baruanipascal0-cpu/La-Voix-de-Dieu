<?php

namespace App\Filament\Resources\ChurchPhotos\Pages;

use App\Filament\Resources\ChurchPhotos\ChurchPhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChurchPhotos extends ListRecords
{
    protected static string $resource = ChurchPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
