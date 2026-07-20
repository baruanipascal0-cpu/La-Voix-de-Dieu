<?php

namespace App\Filament\Resources\RadioStreams\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RadioStreamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Radio')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): string => $set('slug', Str::slug((string) $state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('frequency')
                            ->label('Frequence')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Diffusion')
                    ->schema([
                        TextInput::make('stream_url')
                            ->label('Stream URL')
                            ->required()
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('website_url')
                            ->label('Site web URL')
                            ->url(),
                        TextInput::make('artwork_url')
                            ->label('Image URL')
                            ->url(),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        Toggle::make('is_live')
                            ->label('En direct')
                            ->default(true),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }
}
