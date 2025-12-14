<?php

namespace App\Filament\Resources\Categories\Resources\Food;

use App\Models\Food;

use BackedEnum;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Resources\Food\Pages\CreateFood;
use App\Filament\Resources\Categories\Resources\Food\Pages\EditFood;
use App\Filament\Resources\Categories\Resources\Food\Schemas\FoodForm;
use App\Filament\Resources\Categories\Resources\Food\Tables\FoodTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoodResource extends Resource
{
    protected static ?string $model = Food::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $parentResource = CategoryResource::class;

    public static function getModelLabel(): string
    {
        return __('Món ăn');
    }

    public static function form(Schema $schema): Schema
    {
        return FoodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FoodTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateFood::route('/create'),
            'edit' => EditFood::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->with(['dishes'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
