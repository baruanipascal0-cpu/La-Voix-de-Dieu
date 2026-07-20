<?php

namespace App\Filament\Resources\ChurchInfos;

use App\Filament\Resources\ChurchInfos\Pages\CreateChurchInfo;
use App\Filament\Resources\ChurchInfos\Pages\EditChurchInfo;
use App\Filament\Resources\ChurchInfos\Pages\ListChurchInfos;
use App\Filament\Resources\ChurchInfos\Schemas\ChurchInfoForm;
use App\Filament\Resources\ChurchInfos\Tables\ChurchInfosTable;
use App\Models\ChurchInfo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChurchInfoResource extends Resource
{
    protected static ?string $model = ChurchInfo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Eglise';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'fiche eglise';

    protected static ?string $pluralModelLabel = 'Fiches eglise';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ChurchInfoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChurchInfosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChurchInfos::route('/'),
            'create' => CreateChurchInfo::route('/create'),
            'edit' => EditChurchInfo::route('/{record}/edit'),
        ];
    }
}
