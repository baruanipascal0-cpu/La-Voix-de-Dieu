<?php

namespace App\Filament\Resources\CallSignals\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CallSignalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('signal_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('call.uuid')
                    ->label('Appel')
                    ->limit(12)
                    ->searchable(),
                TextColumn::make('sender.name')
                    ->label('Expediteur')
                    ->searchable(),
                TextColumn::make('recipient.name')
                    ->label('Destinataire')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('signal_type')
                    ->label('Type')
                    ->options([
                        'offer' => 'Offer',
                        'answer' => 'Answer',
                        'candidate' => 'Candidate',
                        'signal' => 'Signal',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
