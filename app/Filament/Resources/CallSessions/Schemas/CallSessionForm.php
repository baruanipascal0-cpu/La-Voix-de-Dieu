<?php

namespace App\Filament\Resources\CallSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CallSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appel')
                    ->schema([
                        TextInput::make('uuid')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(255),
                        Select::make('call_type')
                            ->label('Type')
                            ->options([
                                'dm' => 'Direct',
                                'group' => 'Groupe',
                                'public' => 'Public',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'ringing' => 'Sonnerie',
                                'active' => 'Actif',
                                'ended' => 'Termine',
                                'missed' => 'Manque',
                                'declined' => 'Refuse',
                                'cancelled' => 'Annule',
                            ])
                            ->required(),
                        Select::make('initiator_id')
                            ->label('Initiateur')
                            ->relationship('initiator', 'name')
                            ->searchable(),
                        Select::make('recipient_id')
                            ->label('Destinataire')
                            ->relationship('recipient', 'name')
                            ->searchable(),
                        Select::make('social_group_id')
                            ->label('Groupe')
                            ->relationship('group', 'name')
                            ->searchable(),
                        Select::make('direct_conversation_id')
                            ->label('Conversation DM')
                            ->relationship('conversation', 'uuid')
                            ->searchable(),
                    ])
                    ->columns(2),
                Section::make('Technique')
                    ->schema([
                        TextInput::make('provider')
                            ->label('Provider')
                            ->maxLength(255),
                        TextInput::make('room_name')
                            ->label('Room')
                            ->maxLength(255),
                        TextInput::make('channel_name')
                            ->label('Channel')
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Section::make('Horaires')
                    ->schema([
                        DateTimePicker::make('started_at')
                            ->label('Demarre le'),
                        DateTimePicker::make('ended_at')
                            ->label('Termine le'),
                        DateTimePicker::make('last_state_at')
                            ->label('Dernier etat'),
                    ])
                    ->columns(3),
                Section::make('Meta')
                    ->schema([
                        KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
