<?php

namespace App\Filament\Resources\PastorCalendarEvents\Pages;

use App\Filament\Resources\PastorCalendarEvents\PastorCalendarEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPastorCalendarEvents extends ListRecords
{
    protected static string $resource = PastorCalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
