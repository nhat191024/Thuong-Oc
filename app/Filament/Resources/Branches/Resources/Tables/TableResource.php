<?php

namespace App\Filament\Resources\Branches\Resources\Tables;

use App\Models\Table as TableModel;

use BackedEnum;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Branches\Resources\Tables\Pages\EditTable;
use App\Filament\Resources\Branches\Resources\Tables\Schemas\TableForm;
use App\Filament\Resources\Branches\Resources\Tables\Tables\TablesTable;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TableResource extends Resource
{
    protected static ?string $model = TableModel::class;

    protected static ?string $parentResource = BranchResource::class;

    public static function getModelLabel(): string
    {
        return __('Bàn');
    }

    public static function form(Schema $schema): Schema
    {
        return TableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TablesTable::configure($table);
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
            // 'create' => CreateTable::route('/create'),
            // 'edit' => EditTable::route('/{record}/edit'),
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
