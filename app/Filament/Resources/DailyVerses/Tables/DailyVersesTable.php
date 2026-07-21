<?php

namespace App\Filament\Resources\DailyVerses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DailyVersesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('verse')
                    ->label('Verset')
                    ->limit(70)
                    ->searchable(),
                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Version')
                    ->sortable(),
                TextColumn::make('verse_date')
                    ->label('Date')
                    ->date()
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
                TernaryFilter::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->defaultSort('verse_date', 'desc')
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
