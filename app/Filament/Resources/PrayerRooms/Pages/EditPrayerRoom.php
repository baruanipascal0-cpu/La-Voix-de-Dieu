<?php

namespace App\Filament\Resources\PrayerRooms\Pages;

use App\Filament\Resources\PrayerRooms\PrayerRoomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrayerRoom extends EditRecord
{
    protected static string $resource = PrayerRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
