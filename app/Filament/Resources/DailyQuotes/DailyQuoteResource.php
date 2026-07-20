<?php

namespace App\Filament\Resources\DailyQuotes;

use App\Filament\Resources\DailyQuotes\Pages\CreateDailyQuote;
use App\Filament\Resources\DailyQuotes\Pages\EditDailyQuote;
use App\Filament\Resources\DailyQuotes\Pages\ListDailyQuotes;
use App\Filament\Resources\DailyQuotes\Schemas\DailyQuoteForm;
use App\Filament\Resources\DailyQuotes\Tables\DailyQuotesTable;
use App\Models\DailyQuote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyQuoteResource extends Resource
{
    protected static ?string $model = DailyQuote::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Priere';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'citation';

    protected static ?string $pluralModelLabel = 'Citations du jour';

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return DailyQuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyQuotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyQuotes::route('/'),
            'create' => CreateDailyQuote::route('/create'),
            'edit' => EditDailyQuote::route('/{record}/edit'),
        ];
    }
}
