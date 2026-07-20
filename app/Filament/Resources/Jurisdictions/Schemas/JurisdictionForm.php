<?php

namespace App\Filament\Resources\Jurisdictions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class JurisdictionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Juridiction')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('leader_name')
                            ->label('Responsable')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Coordonnees')
                    ->schema([
                        Textarea::make('address')
                            ->label('Adresse')
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label('Telephone')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
