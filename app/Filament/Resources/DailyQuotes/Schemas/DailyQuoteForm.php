<?php

namespace App\Filament\Resources\DailyQuotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyQuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Citation')
                    ->schema([
                        Textarea::make('quote')
                            ->label('Citation')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('reference')
                            ->label('Reference')
                            ->maxLength(255),
                        TextInput::make('author')
                            ->label('Auteur')
                            ->maxLength(255),
                        DatePicker::make('quote_date')
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
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
