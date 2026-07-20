<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temoignage')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(255),
                        TextInput::make('author')
                            ->label('Auteur')
                            ->maxLength(255),
                        TextInput::make('role')
                            ->label('Role ou precision')
                            ->maxLength(255),
                        DateTimePicker::make('published_at')
                            ->label('Date de publication'),
                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
                            ->columnSpanFull(),
                        Textarea::make('content')
                            ->label('Contenu')
                            ->required()
                            ->rows(6)
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
                            ->label('Publie')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
