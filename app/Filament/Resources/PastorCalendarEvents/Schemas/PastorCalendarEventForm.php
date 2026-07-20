<?php

namespace App\Filament\Resources\PastorCalendarEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PastorCalendarEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evenement')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        Select::make('event_type')
                            ->label('Type')
                            ->options([
                                'appointment' => 'Rendez-vous',
                                'visit' => 'Visite',
                                'service' => 'Culte',
                                'meeting' => 'Reunion',
                                'travel' => 'Deplacement',
                            ])
                            ->default('appointment')
                            ->required(),
                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Horaire')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Debut'),
                        DateTimePicker::make('ends_at')
                            ->label('Fin'),
                        Toggle::make('is_public')
                            ->label('Public')
                            ->default(true),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
