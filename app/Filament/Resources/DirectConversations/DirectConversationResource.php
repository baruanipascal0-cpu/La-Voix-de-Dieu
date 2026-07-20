<?php

namespace App\Filament\Resources\DirectConversations;

use App\Filament\Resources\DirectConversations\Pages\EditDirectConversation;
use App\Filament\Resources\DirectConversations\Pages\ListDirectConversations;
use App\Filament\Resources\DirectConversations\Schemas\DirectConversationForm;
use App\Filament\Resources\DirectConversations\Tables\DirectConversationsTable;
use App\Models\DirectConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DirectConversationResource extends Resource
{
    protected static ?string $model = DirectConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'conversation privee';

    protected static ?string $pluralModelLabel = 'Conversations privees';

    protected static ?string $recordTitleAttribute = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return DirectConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectConversationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDirectConversations::route('/'),
            'edit' => EditDirectConversation::route('/{record}/edit'),
        ];
    }
}
