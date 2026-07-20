<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Programme')
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
                        Select::make('day_of_week')
                            ->label('Jour')
                            ->options([
                                0 => 'Dimanche',
                                1 => 'Lundi',
                                2 => 'Mardi',
                                3 => 'Mercredi',
                                4 => 'Jeudi',
                                5 => 'Vendredi',
                                6 => 'Samedi',
                            ]),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Horaire')
                    ->schema([
                        TimePicker::make('starts_at')
                            ->label('Debut')
                            ->seconds(false),
                        TimePicker::make('ends_at')
                            ->label('Fin')
                            ->seconds(false),
                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255),
                        TextInput::make('speaker')
                            ->label('Intervenant')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label('Mis en avant')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Actif')
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
