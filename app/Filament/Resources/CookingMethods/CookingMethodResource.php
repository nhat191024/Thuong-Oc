<?php

namespace App\Filament\Resources\CookingMethods;

use App\Models\CookingMethod;

use BackedEnum;

use App\Filament\Resources\CookingMethods\Pages\CreateCookingMethod;
use App\Filament\Resources\CookingMethods\Pages\EditCookingMethod;
use App\Filament\Resources\CookingMethods\Pages\ListCookingMethods;
use App\Filament\Resources\CookingMethods\Schemas\CookingMethodForm;
use App\Filament\Resources\CookingMethods\Tables\CookingMethodsTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CookingMethodResource extends Resource
{
    protected static ?string $model = CookingMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('Phương pháp nấu');
    }

    public static function form(Schema $schema): Schema
    {
        return CookingMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CookingMethodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withExists('dishes');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCookingMethods::route('/'),
            // 'create' => CreateCookingMethod::route('/create'),
            // 'edit' => EditCookingMethod::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
