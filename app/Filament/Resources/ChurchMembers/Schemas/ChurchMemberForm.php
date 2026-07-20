<?php

namespace App\Filament\Resources\ChurchMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChurchMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identite')
                    ->schema([
                        TextInput::make('display_name')
                            ->label('Nom affiche')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('first_name')
                            ->label('Prenom')
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Nom')
                            ->maxLength(255),
                        Select::make('gender')
                            ->label('Genre')
                            ->options([
                                'male' => 'Homme',
                                'female' => 'Femme',
                            ]),
                        TextInput::make('avatar_url')
                            ->label('Avatar URL')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Eglise')
                    ->schema([
                        TextInput::make('jurisdiction')
                            ->label('Juridiction')
                            ->maxLength(255),
                        Select::make('member_type')
                            ->label('Type')
                            ->options([
                                'member' => 'Membre',
                                'deacon' => 'Diacre',
                                'pastor' => 'Pasteur',
                                'visitor' => 'Visiteur',
                            ]),
                        DatePicker::make('joined_at')
                            ->label('Date entree'),
                    ])
                    ->columns(2),
                Section::make('Contacts')
                    ->schema([
                        TextInput::make('phone')
                            ->label('Telephone')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Toggle::make('show_contacts')
                            ->label('Afficher les contacts')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
