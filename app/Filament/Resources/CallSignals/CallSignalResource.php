<?php

namespace App\Filament\Resources\CallSignals;

use App\Filament\Resources\CallSignals\Pages\EditCallSignal;
use App\Filament\Resources\CallSignals\Pages\ListCallSignals;
use App\Filament\Resources\CallSignals\Schemas\CallSignalForm;
use App\Filament\Resources\CallSignals\Tables\CallSignalsTable;
use App\Models\CallSignal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CallSignalResource extends Resource
{
    protected static ?string $model = CallSignal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = 'signal appel';

    protected static ?string $pluralModelLabel = 'Signaux appels';

    protected static ?string $recordTitleAttribute = 'signal_type';

    public static function form(Schema $schema): Schema
    {
        return CallSignalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallSignalsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCallSignals::route('/'),
            'edit' => EditCallSignal::route('/{record}/edit'),
        ];
    }
}
