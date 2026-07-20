<?php

namespace App\Filament\Resources\PrayerRooms;

use App\Filament\Resources\PrayerRooms\Pages\CreatePrayerRoom;
use App\Filament\Resources\PrayerRooms\Pages\EditPrayerRoom;
use App\Filament\Resources\PrayerRooms\Pages\ListPrayerRooms;
use App\Filament\Resources\PrayerRooms\Schemas\PrayerRoomForm;
use App\Filament\Resources\PrayerRooms\Tables\PrayerRoomsTable;
use App\Models\PrayerRoom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrayerRoomResource extends Resource
{
    protected static ?string $model = PrayerRoom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|UnitEnum|null $navigationGroup = 'Priere';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'salle de priere';

    protected static ?string $pluralModelLabel = 'Salles de priere';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PrayerRoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrayerRoomsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrayerRooms::route('/'),
            'create' => CreatePrayerRoom::route('/create'),
            'edit' => EditPrayerRoom::route('/{record}/edit'),
        ];
    }
}
