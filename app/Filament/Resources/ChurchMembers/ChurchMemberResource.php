<?php

namespace App\Filament\Resources\ChurchMembers;

use App\Filament\Resources\ChurchMembers\Pages\CreateChurchMember;
use App\Filament\Resources\ChurchMembers\Pages\EditChurchMember;
use App\Filament\Resources\ChurchMembers\Pages\ListChurchMembers;
use App\Filament\Resources\ChurchMembers\Schemas\ChurchMemberForm;
use App\Filament\Resources\ChurchMembers\Tables\ChurchMembersTable;
use App\Models\ChurchMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChurchMemberResource extends Resource
{
    protected static ?string $model = ChurchMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Eglise';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'membre';

    protected static ?string $pluralModelLabel = 'Membres';

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function form(Schema $schema): Schema
    {
        return ChurchMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChurchMembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChurchMembers::route('/'),
            'create' => CreateChurchMember::route('/create'),
            'edit' => EditChurchMember::route('/{record}/edit'),
        ];
    }
}
