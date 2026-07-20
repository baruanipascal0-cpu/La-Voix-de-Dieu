<?php

namespace App\Filament\Resources\SocialGroups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SocialGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
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
                        Select::make('created_by')
                            ->label('Createur')
                            ->relationship('creator', 'name')
                            ->searchable(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'community' => 'Communaute',
                                'prayer' => 'Priere',
                                'committee' => 'Comite',
                                'youth' => 'Jeunesse',
                            ])
                            ->default('community')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Images')
                    ->schema([
                        TextInput::make('avatar_url')
                            ->label('Avatar URL')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('cover_url')
                            ->label('Couverture URL')
                            ->url()
                            ->columnSpanFull(),
                    ]),
                Section::make('Acces')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Approuve',
                                'rejected' => 'Rejete',
                                'suspended' => 'Suspendu',
                                'blocked' => 'Bloque',
                            ])
                            ->default('approved')
                            ->required(),
                        Toggle::make('is_public')
                            ->label('Public')
                            ->default(true),
                        Toggle::make('requires_approval')
                            ->label('Approbation requise')
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
                Section::make('Moderation')
                    ->schema([
                        Select::make('approved_by')
                            ->label('Approuve par')
                            ->relationship('approver', 'name')
                            ->searchable(),
                        TextInput::make('moderation_reason')
                            ->label('Raison')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
