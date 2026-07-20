<?php

namespace App\Filament\Resources\RadioStreams;

use App\Filament\Resources\RadioStreams\Pages\CreateRadioStream;
use App\Filament\Resources\RadioStreams\Pages\EditRadioStream;
use App\Filament\Resources\RadioStreams\Pages\ListRadioStreams;
use App\Filament\Resources\RadioStreams\Schemas\RadioStreamForm;
use App\Filament\Resources\RadioStreams\Tables\RadioStreamsTable;
use App\Models\RadioStream;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RadioStreamResource extends Resource
{
    protected static ?string $model = RadioStream::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRadio;

    protected static string|UnitEnum|null $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'radio';

    protected static ?string $pluralModelLabel = 'Radio';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return RadioStreamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RadioStreamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRadioStreams::route('/'),
            'create' => CreateRadioStream::route('/create'),
            'edit' => EditRadioStream::route('/{record}/edit'),
        ];
    }
}
