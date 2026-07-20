<?php

namespace App\Filament\Resources\PastorCalendarEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PastorCalendarEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lieu')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('starts_at')
                    ->label('Debut')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Type')
                    ->options([
                        'appointment' => 'Rendez-vous',
                        'visit' => 'Visite',
                        'service' => 'Culte',
                        'meeting' => 'Reunion',
                        'travel' => 'Deplacement',
                    ]),
                TernaryFilter::make('is_public')
                    ->label('Public')
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
