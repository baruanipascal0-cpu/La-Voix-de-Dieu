<?php

namespace App\Filament\Resources\CallSessions;

use App\Filament\Resources\CallSessions\Pages\EditCallSession;
use App\Filament\Resources\CallSessions\Pages\ListCallSessions;
use App\Filament\Resources\CallSessions\Schemas\CallSessionForm;
use App\Filament\Resources\CallSessions\Tables\CallSessionsTable;
use App\Models\CallSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CallSessionResource extends Resource
{
    protected static ?string $model = CallSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'appel';

    protected static ?string $pluralModelLabel = 'Appels';

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return CallSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCallSessions::route('/'),
            'edit' => EditCallSession::route('/{record}/edit'),
        ];
    }
}
