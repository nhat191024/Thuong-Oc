<?php

namespace App\Filament\Resources\InventoryUnits;

use App\Filament\Resources\InventoryUnits\Pages\CreateInventoryUnit;
use App\Filament\Resources\InventoryUnits\Pages\EditInventoryUnit;
use App\Filament\Resources\InventoryUnits\Pages\ListInventoryUnits;
use App\Filament\Resources\InventoryUnits\Schemas\InventoryUnitForm;
use App\Filament\Resources\InventoryUnits\Tables\InventoryUnitsTable;
use App\Models\InventoryUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryUnitResource extends Resource
{
    protected static ?string $model = InventoryUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('Đơn vị tồn kho');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Đơn vị tồn kho');
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withExists('foods');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryUnits::route('/'),
            // 'create' => CreateInventoryUnit::route('/create'),
            // 'edit' => EditInventoryUnit::route('/{record}/edit'),
        ];
    }
}
