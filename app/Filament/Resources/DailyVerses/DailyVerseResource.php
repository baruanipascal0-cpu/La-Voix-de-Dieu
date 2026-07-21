<?php

namespace App\Filament\Resources\DailyVerses;

use App\Filament\Resources\DailyVerses\Pages\CreateDailyVerse;
use App\Filament\Resources\DailyVerses\Pages\EditDailyVerse;
use App\Filament\Resources\DailyVerses\Pages\ListDailyVerses;
use App\Filament\Resources\DailyVerses\Schemas\DailyVerseForm;
use App\Filament\Resources\DailyVerses\Tables\DailyVersesTable;
use App\Models\DailyVerse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyVerseResource extends Resource
{
    protected static ?string $model = DailyVerse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Priere';

    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = 'verset';

    protected static ?string $pluralModelLabel = 'Versets du jour';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return DailyVerseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyVersesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyVerses::route('/'),
            'create' => CreateDailyVerse::route('/create'),
            'edit' => EditDailyVerse::route('/{record}/edit'),
        ];
    }
}
