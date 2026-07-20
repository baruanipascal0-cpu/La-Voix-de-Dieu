<?php

namespace App\Filament\Resources\PastorCalendarEvents\Pages;

use App\Filament\Resources\PastorCalendarEvents\PastorCalendarEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPastorCalendarEvent extends EditRecord
{
    protected static string $resource = PastorCalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
