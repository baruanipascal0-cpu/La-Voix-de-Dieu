<?php

namespace App\Filament\Resources\CallSessions\Pages;

use App\Filament\Resources\CallSessions\CallSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListCallSessions extends ListRecords
{
    protected static string $resource = CallSessionResource::class;
}
