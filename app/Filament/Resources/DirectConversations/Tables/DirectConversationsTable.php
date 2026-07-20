<?php

namespace App\Filament\Resources\DirectConversations\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DirectConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->limit(12)
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Sujet')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->label('Cree par')
                    ->searchable(),
                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('participants')
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('last_message_at')
                    ->label('Dernier message')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
