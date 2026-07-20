<?php

namespace App\Filament\Resources\Jurisdictions;

use App\Filament\Resources\Jurisdictions\Pages\CreateJurisdiction;
use App\Filament\Resources\Jurisdictions\Pages\EditJurisdiction;
use App\Filament\Resources\Jurisdictions\Pages\ListJurisdictions;
use App\Filament\Resources\Jurisdictions\Schemas\JurisdictionForm;
use App\Filament\Resources\Jurisdictions\Tables\JurisdictionsTable;
use App\Models\Jurisdiction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JurisdictionResource extends Resource
{
    protected static ?string $model = Jurisdiction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Eglise';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'juridiction';

    protected static ?string $pluralModelLabel = 'Juridictions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return JurisdictionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JurisdictionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJurisdictions::route('/'),
            'create' => CreateJurisdiction::route('/create'),
            'edit' => EditJurisdiction::route('/{record}/edit'),
        ];
    }
}
