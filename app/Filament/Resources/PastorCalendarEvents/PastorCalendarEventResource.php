<?php

namespace App\Filament\Resources\PastorCalendarEvents;

use App\Filament\Resources\PastorCalendarEvents\Pages\CreatePastorCalendarEvent;
use App\Filament\Resources\PastorCalendarEvents\Pages\EditPastorCalendarEvent;
use App\Filament\Resources\PastorCalendarEvents\Pages\ListPastorCalendarEvents;
use App\Filament\Resources\PastorCalendarEvents\Schemas\PastorCalendarEventForm;
use App\Filament\Resources\PastorCalendarEvents\Tables\PastorCalendarEventsTable;
use App\Models\PastorCalendarEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PastorCalendarEventResource extends Resource
{
    protected static ?string $model = PastorCalendarEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Priere';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'agenda pastoral';

    protected static ?string $pluralModelLabel = 'Agenda pastoral';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PastorCalendarEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PastorCalendarEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPastorCalendarEvents::route('/'),
            'create' => CreatePastorCalendarEvent::route('/create'),
            'edit' => EditPastorCalendarEvent::route('/{record}/edit'),
        ];
    }
}
