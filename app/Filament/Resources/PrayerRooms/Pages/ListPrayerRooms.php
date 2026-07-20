<?php

namespace App\Filament\Resources\PrayerRooms\Pages;

use App\Filament\Resources\PrayerRooms\PrayerRoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrayerRooms extends ListRecords
{
    protected static string $resource = PrayerRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
