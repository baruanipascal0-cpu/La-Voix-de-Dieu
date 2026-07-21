<?php

namespace App\Filament\Resources\DailyVerses\Pages;

use App\Filament\Resources\DailyVerses\DailyVerseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyVerse extends CreateRecord
{
    protected static string $resource = DailyVerseResource::class;
}
