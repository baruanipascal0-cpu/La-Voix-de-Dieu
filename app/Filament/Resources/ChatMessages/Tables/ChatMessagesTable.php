<?php

namespace App\Filament\Resources\ChatMessages\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChatMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scope')
                    ->label('Portee')
                    ->badge()
                    ->sortable(),
                TextColumn::make('message_type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
                TextColumn::make('sender.name')
                    ->label('Expediteur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Message')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('group.name')
                    ->label('Groupe')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('conversation.uuid')
                    ->label('Conversation')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('call.uuid')
                    ->label('Appel')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->label('Portee')
                    ->options([
                        'public' => 'Public',
                        'group' => 'Groupe',
                        'dm' => 'Prive',
                        'call' => 'Appel',
                    ]),
                SelectFilter::make('message_type')
                    ->label('Type')
                    ->options([
                        'text' => 'Texte',
                        'audio' => 'Audio',
                        'image' => 'Image',
                        'video' => 'Video',
                        'system' => 'Systeme',
                    ]),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'published' => 'Publie',
                        'pending' => 'En attente',
                        'reported' => 'Signale',
                        'removed' => 'Supprime',
                        'blocked' => 'Bloque',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
