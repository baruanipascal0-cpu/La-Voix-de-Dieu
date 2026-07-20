<?php

namespace App\Filament\Resources\DirectConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DirectConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation')
                    ->schema([
                        TextInput::make('uuid')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('subject')
                            ->label('Sujet')
                            ->maxLength(255),
                        Select::make('created_by')
                            ->label('Cree par')
                            ->relationship('creator', 'name')
                            ->searchable(),
                        DateTimePicker::make('last_message_at')
                            ->label('Dernier message'),
                    ])
                    ->columns(2),
            ]);
    }
}
