<?php

namespace App\Filament\Resources\ChurchMembers\Pages;

use App\Filament\Resources\ChurchMembers\ChurchMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChurchMember extends EditRecord
{
    protected static string $resource = ChurchMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
