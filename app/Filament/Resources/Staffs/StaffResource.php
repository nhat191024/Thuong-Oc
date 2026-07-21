<?php

namespace App\Filament\Resources\Staffs;

use App\Enums\Role;
use App\Models\User;

use App\Filament\Resources\Staffs\Pages\CreateStaff;
use App\Filament\Resources\Staffs\Pages\EditStaff;
use App\Filament\Resources\Staffs\Pages\ListStaffs;
use App\Filament\Resources\Staffs\Schemas\StaffForm;
use App\Filament\Resources\Staffs\Tables\StaffsTable;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return __('Nhân viên');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Quản lý nhân viên');
    }

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'branch'])
            ->whereHas('roles', function (Builder $query) {
                $query->whereIn('name', [Role::STAFF->value, Role::TABLE_ADMIN->value, Role::KITCHEN->value]);
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffs::route('/'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
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
