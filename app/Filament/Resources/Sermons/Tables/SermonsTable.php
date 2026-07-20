<?php

namespace App\Filament\Resources\Sermons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SermonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Image')
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('preacher')
                    ->label('Predicateur')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Publie le')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('A la une')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Publie')
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
                SelectFilter::make('category')
                    ->label('Categorie')
                    ->relationship('category', 'name')
                    ->searchable(),
                TernaryFilter::make('is_featured')
                    ->label('A la une')
                    ->boolean(),
                TernaryFilter::make('is_published')
                    ->label('Publie')
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
