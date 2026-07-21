<?php

namespace App\Filament\Resources\DailyVerses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyVerseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Verset')
                    ->schema([
                        Textarea::make('verse')
                            ->label('Texte biblique')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('reference')
                            ->label('Reference biblique')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('version')
                            ->label('Version')
                            ->maxLength(80),
                        DatePicker::make('verse_date')
                            ->label('Date'),
                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
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
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
