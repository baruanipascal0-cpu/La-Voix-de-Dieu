<?php

namespace App\Filament\Resources\LiveStreams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LiveStreamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Direct')
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
                        Select::make('platform')
                            ->label('Plateforme')
                            ->options([
                                'youtube' => 'YouTube',
                                'facebook' => 'Facebook',
                                'custom' => 'Autre',
                            ])
                            ->default('youtube')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Diffusion')
                    ->schema([
                        TextInput::make('stream_url')
                            ->label('Stream URL')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('playback_url')
                            ->label('Playback URL')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url(),
                        TextInput::make('youtube_id')
                            ->label('YouTube ID')
                            ->maxLength(255),
                        TextInput::make('thumbnail_url')
                            ->label('Image URL')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Debut'),
                        DateTimePicker::make('ends_at')
                            ->label('Fin'),
                        Toggle::make('is_live')
                            ->label('En direct')
                            ->default(false),
                        Toggle::make('is_published')
                            ->label('Publie')
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
