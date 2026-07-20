<?php

namespace App\Filament\Resources\ChurchPhotos;

use App\Filament\Resources\ChurchPhotos\Pages\CreateChurchPhoto;
use App\Filament\Resources\ChurchPhotos\Pages\EditChurchPhoto;
use App\Filament\Resources\ChurchPhotos\Pages\ListChurchPhotos;
use App\Filament\Resources\ChurchPhotos\Schemas\ChurchPhotoForm;
use App\Filament\Resources\ChurchPhotos\Tables\ChurchPhotosTable;
use App\Models\ChurchPhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChurchPhotoResource extends Resource
{
    protected static ?string $model = ChurchPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Eglise';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = 'photo';

    protected static ?string $pluralModelLabel = 'Photos';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ChurchPhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChurchPhotosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChurchPhotos::route('/'),
            'create' => CreateChurchPhoto::route('/create'),
            'edit' => EditChurchPhoto::route('/{record}/edit'),
        ];
    }
}
