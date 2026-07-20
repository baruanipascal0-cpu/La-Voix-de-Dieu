<?php

namespace App\Filament\Resources\ChurchMembers\Pages;

use App\Filament\Resources\ChurchMembers\ChurchMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChurchMembers extends ListRecords
{
    protected static string $resource = ChurchMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
