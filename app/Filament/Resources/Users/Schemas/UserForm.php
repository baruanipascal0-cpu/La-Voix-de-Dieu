<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telephone')
                            ->maxLength(255),
                        TextInput::make('avatar_url')
                            ->label('Avatar URL')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Acces')
                    ->schema([
                        Select::make('role')
                            ->label('Role simple')
                            ->options([
                                'super_admin' => 'Super admin',
                                'admin' => 'Admin',
                                'editor' => 'Editeur',
                                'moderator' => 'Moderateur',
                                'media_manager' => 'Media',
                                'prayer_leader' => 'Priere',
                                'member' => 'Membre',
                            ])
                            ->default('member')
                            ->required(),
                        Select::make('roles')
                            ->label('Roles avances')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make('Moderation')
                    ->schema([
                        DateTimePicker::make('suspended_at')
                            ->label('Suspendu le'),
                        DateTimePicker::make('blocked_at')
                            ->label('Bloque le'),
                        Textarea::make('moderation_reason')
                            ->label('Raison')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
