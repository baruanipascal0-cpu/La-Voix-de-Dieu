<?php

namespace App\Filament\Resources\ChurchPhotos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChurchPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Photo')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(255),
                        DateTimePicker::make('taken_at')
                            ->label('Date photo'),
                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('thumbnail_url')
                            ->label('Miniature URL')
                            ->url()
                            ->columnSpanFull(),
                        Textarea::make('caption')
                            ->label('Legende')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Publication')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_published')
                            ->label('Publiee')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
