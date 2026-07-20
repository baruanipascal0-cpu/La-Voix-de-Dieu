<?php

namespace App\Filament\Resources\CallSignals\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CallSignalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Signal')
                    ->schema([
                        Select::make('call_session_id')
                            ->label('Appel')
                            ->relationship('call', 'uuid')
                            ->searchable()
                            ->required(),
                        TextInput::make('signal_type')
                            ->label('Type')
                            ->required()
                            ->maxLength(255),
                        Select::make('sender_id')
                            ->label('Expediteur')
                            ->relationship('sender', 'name')
                            ->searchable(),
                        Select::make('recipient_id')
                            ->label('Destinataire')
                            ->relationship('recipient', 'name')
                            ->searchable(),
                    ])
                    ->columns(2),
                Section::make('Payload')
                    ->schema([
                        KeyValue::make('payload')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
