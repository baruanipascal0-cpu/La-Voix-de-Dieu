<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->formatStateUsing(fn (?int $state): string => match ($state) {
                        0 => 'Dimanche',
                        1 => 'Lundi',
                        2 => 'Mardi',
                        3 => 'Mercredi',
                        4 => 'Jeudi',
                        5 => 'Vendredi',
                        6 => 'Samedi',
                        default => '-',
                    })
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Debut')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fin')
                    ->time('H:i')
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label('Avant')
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
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label('Jour')
                    ->options([
                        0 => 'Dimanche',
                        1 => 'Lundi',
                        2 => 'Mardi',
                        3 => 'Mercredi',
                        4 => 'Jeudi',
                        5 => 'Vendredi',
                        6 => 'Samedi',
                    ]),
                TernaryFilter::make('is_featured')
                    ->label('Mis en avant')
                    ->boolean(),
                TernaryFilter::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
