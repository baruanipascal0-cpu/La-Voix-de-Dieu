<?php

namespace App\Filament\Resources\ChurchInfos\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChurchInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identite')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('tagline')
                            ->label('Slogan')
                            ->maxLength(255),
                        Textarea::make('about')
                            ->label('A propos')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Coordonnees')
                    ->schema([
                        Textarea::make('address')
                            ->label('Adresse')
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->label('Pays')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telephone')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('website_url')
                            ->label('Site web')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('map_url')
                            ->label('Lien carte')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->numeric(),
                        TextInput::make('longitude')
                            ->numeric(),
                    ])
                    ->columns(2),
                Section::make('Images')
                    ->schema([
                        TextInput::make('logo_url')
                            ->label('Logo URL')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('cover_url')
                            ->label('Couverture URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),
                Section::make('Horaires et reseaux')
                    ->schema([
                        KeyValue::make('service_times')
                            ->label('Horaires')
                            ->columnSpanFull(),
                        KeyValue::make('social_links')
                            ->label('Reseaux sociaux')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
