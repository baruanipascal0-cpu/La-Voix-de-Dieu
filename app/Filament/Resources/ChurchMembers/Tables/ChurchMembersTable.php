<?php

namespace App\Filament\Resources\ChurchMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ChurchMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular(),
                TextColumn::make('display_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jurisdiction')
                    ->label('Juridiction')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telephone')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('show_contacts')
                    ->label('Contacts')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('joined_at')
                    ->label('Entree')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('member_type')
                    ->label('Type')
                    ->options([
                        'member' => 'Membre',
                        'deacon' => 'Diacre',
                        'pastor' => 'Pasteur',
                        'visitor' => 'Visiteur',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TernaryFilter::make('show_contacts')
                    ->label('Contacts visibles')
                    ->boolean(),
            ])
            ->defaultSort('display_name')
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
