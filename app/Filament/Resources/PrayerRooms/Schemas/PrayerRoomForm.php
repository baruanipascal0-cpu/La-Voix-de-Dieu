<?php

namespace App\Filament\Resources\PrayerRooms\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PrayerRoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Salle de priere')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('room_type')
                            ->label('Type')
                            ->options([
                                'general' => 'Generale',
                                'youth' => 'Jeunesse',
                                'women' => 'Femmes',
                                'men' => 'Hommes',
                                'intercession' => 'Intercession',
                            ])
                            ->default('general')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Connexion')
                    ->schema([
                        TextInput::make('meeting_url')
                            ->label('Lien reunion')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('livekit_room')
                            ->label('Salle LiveKit')
                            ->maxLength(255),
                    ]),
                Section::make('Horaire et statut')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Debut'),
                        DateTimePicker::make('ends_at')
                            ->label('Fin'),
                        Toggle::make('is_live')
                            ->label('En direct')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
