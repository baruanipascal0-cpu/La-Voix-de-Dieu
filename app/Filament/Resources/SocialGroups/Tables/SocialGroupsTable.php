<?php

namespace App\Filament\Resources\SocialGroups\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SocialGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Createur')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('requires_approval')
                    ->label('Approbation')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifie le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'community' => 'Communaute',
                        'prayer' => 'Priere',
                        'committee' => 'Comite',
                        'youth' => 'Jeunesse',
                    ]),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuve',
                        'rejected' => 'Rejete',
                        'suspended' => 'Suspendu',
                        'blocked' => 'Bloque',
                    ]),
                TernaryFilter::make('is_public')
                    ->label('Public')
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
