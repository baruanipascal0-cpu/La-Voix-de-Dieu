<?php

namespace App\Filament\Resources\Sermons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SermonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
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
                        TextInput::make('subtitle')
                            ->label('Sous-titre')
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Categorie')
                            ->relationship('category', 'name')
                            ->searchable(),
                        TextInput::make('preacher')
                            ->label('Predicateur')
                            ->maxLength(255),
                        TextInput::make('scripture_reference')
                            ->label('Reference biblique')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Medias')
                    ->schema([
                        TextInput::make('audio_url')
                            ->label('Audio URL')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('video_url')
                            ->label('Video URL')
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
                        TextInput::make('duration_seconds')
                            ->label('Duree en secondes')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        DateTimePicker::make('published_at')
                            ->label('Publie le'),
                        Toggle::make('is_featured')
                            ->label('A la une')
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
