<?php

namespace App\Filament\Resources\ChurchMembers\Pages;

use App\Filament\Resources\ChurchMembers\ChurchMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChurchMember extends CreateRecord
{
    protected static string $resource = ChurchMemberResource::class;
}
