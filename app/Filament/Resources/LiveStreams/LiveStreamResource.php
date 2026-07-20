<?php

namespace App\Filament\Resources\LiveStreams;

use App\Filament\Resources\LiveStreams\Pages\CreateLiveStream;
use App\Filament\Resources\LiveStreams\Pages\EditLiveStream;
use App\Filament\Resources\LiveStreams\Pages\ListLiveStreams;
use App\Filament\Resources\LiveStreams\Schemas\LiveStreamForm;
use App\Filament\Resources\LiveStreams\Tables\LiveStreamsTable;
use App\Models\LiveStream;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LiveStreamResource extends Resource
{
    protected static ?string $model = LiveStream::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string|UnitEnum|null $navigationGroup = 'Contenu';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'direct';

    protected static ?string $pluralModelLabel = 'Directs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LiveStreamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LiveStreamsTable::configure($table);
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
            'index' => ListLiveStreams::route('/'),
            'create' => CreateLiveStream::route('/create'),
            'edit' => EditLiveStream::route('/{record}/edit'),
        ];
    }
}
