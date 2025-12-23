<?php

namespace App\Filament\Resources\Bills;

use App\Models\Bill;

use BackedEnum;

use App\Filament\Resources\Bills\Pages\CreateBill;
use App\Filament\Resources\Bills\Pages\EditBill;
use App\Filament\Resources\Bills\Pages\ListBills;
use App\Filament\Resources\Bills\Pages\ViewBill;
use App\Filament\Resources\Bills\Tables\BillsTable;

use App\Filament\Resources\Bills\Schemas\BillForm;
use App\Filament\Resources\Bills\Schemas\BillInfolist;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;


class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function getModelLabel(): string
    {
        return __('Hoá đơn');
    }

    public static function infolist(Schema $schema): Schema
    {
        return BillInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillsTable::configure($table);
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
            ->with(['table', 'branch', 'user', 'billDetails.dish.food', 'billDetails.dish.cookingMethod']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBills::route('/'),
            // 'create' => CreateBill::route('/create'),
            // 'edit' => EditBill::route('/{record}/edit'),
            'view' => ViewBill::route('/{record}'),
        ];
    }
}
