<?php

namespace App\Filament\Resources\DirectConversations\Pages;

use App\Filament\Resources\DirectConversations\DirectConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListDirectConversations extends ListRecords
{
    protected static string $resource = DirectConversationResource::class;
}
