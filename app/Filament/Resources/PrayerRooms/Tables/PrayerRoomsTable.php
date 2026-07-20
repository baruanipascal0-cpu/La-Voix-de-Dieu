<?php

namespace App\Filament\Resources\PrayerRooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PrayerRoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('room_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Debut')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('room_type')
                    ->label('Type')
                    ->options([
                        'general' => 'Generale',
                        'youth' => 'Jeunesse',
                        'women' => 'Femmes',
                        'men' => 'Hommes',
                        'intercession' => 'Intercession',
                    ]),
                TernaryFilter::make('is_live')
                    ->label('En direct')
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label('Active')
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
