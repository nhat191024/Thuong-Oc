<?php

namespace App\Filament\Resources\Staffs\Schemas;

use App\Enums\Role;
use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label(__('Tên đăng nhập'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('Mật khẩu'))
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->maxLength(255),
                TextInput::make('name')
                    ->label(__('Họ và tên'))
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('Số điện thoại'))
                    ->tel()
                    ->maxLength(20),
                Select::make('branch_id')
                    ->label(__('Chi nhánh'))
                    ->options(Branch::query()->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('role')
                    ->label(__('Vai trò'))
                    ->options([
                        Role::STAFF->value => __('Nhân viên'),
                        Role::KITCHEN->value => __('Bếp'),
                    ])
                    ->required()
                    ->native(false),
            ]);
    }
}
