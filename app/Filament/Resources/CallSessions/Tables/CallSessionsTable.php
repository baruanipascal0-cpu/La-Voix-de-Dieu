<?php

namespace App\Filament\Resources\CallSessions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CallSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('call_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                TextColumn::make('initiator.name')
                    ->label('Initiateur')
                    ->searchable(),
                TextColumn::make('recipient.name')
                    ->label('Destinataire')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('group.name')
                    ->label('Groupe')
                    ->toggleable(),
                TextColumn::make('room_name')
                    ->label('Room')
                    ->limit(24)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Termine le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('call_type')
                    ->label('Type')
                    ->options([
                        'dm' => 'Direct',
                        'group' => 'Groupe',
                        'public' => 'Public',
                    ]),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'ringing' => 'Sonnerie',
                        'active' => 'Actif',
                        'ended' => 'Termine',
                        'missed' => 'Manque',
                        'declined' => 'Refuse',
                        'cancelled' => 'Annule',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
