<?php

namespace App\Filament\Resources\ChatMessages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChatMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
                    ->schema([
                        TextInput::make('uuid')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('scope')
                            ->label('Portee')
                            ->options([
                                'public' => 'Public',
                                'group' => 'Groupe',
                                'dm' => 'Prive',
                                'call' => 'Appel',
                            ])
                            ->required(),
                        Select::make('message_type')
                            ->label('Type')
                            ->options([
                                'text' => 'Texte',
                                'audio' => 'Audio',
                                'image' => 'Image',
                                'video' => 'Video',
                                'system' => 'Systeme',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'published' => 'Publie',
                                'pending' => 'En attente',
                                'reported' => 'Signale',
                                'removed' => 'Supprime',
                                'blocked' => 'Bloque',
                            ])
                            ->default('published')
                            ->required(),
                        Select::make('sender_id')
                            ->label('Expediteur')
                            ->relationship('sender', 'name')
                            ->searchable(),
                        Textarea::make('body')
                            ->label('Texte')
                            ->columnSpanFull(),
                        TextInput::make('media_url')
                            ->label('Media URL')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Contextes')
                    ->schema([
                        Select::make('social_group_id')
                            ->label('Groupe')
                            ->relationship('group', 'name')
                            ->searchable(),
                        Select::make('direct_conversation_id')
                            ->label('Conversation DM')
                            ->relationship('conversation', 'uuid')
                            ->searchable(),
                        Select::make('call_session_id')
                            ->label('Appel')
                            ->relationship('call', 'uuid')
                            ->searchable(),
                        DateTimePicker::make('edited_at')
                            ->label('Modifie le'),
                    ])
                    ->columns(2),
                Section::make('Meta')
                    ->schema([
                        DateTimePicker::make('reported_at')
                            ->label('Signale le'),
                        Select::make('moderated_by')
                            ->label('Modere par')
                            ->relationship('moderator', 'name')
                            ->searchable(),
                        DateTimePicker::make('moderated_at')
                            ->label('Modere le'),
                        Textarea::make('moderation_reason')
                            ->label('Raison moderation')
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
