<?php

namespace App\Filament\Resources\Printers\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PrinterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label(__('Chi nhánh'))
                    ->options(Branch::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->label(__('Tên máy in'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('ip_address')
                    ->label(__('Địa chỉ IP'))
                    ->required()
                    ->placeholder('192.168.1.250')
                    ->rules(['ip'])
                    ->maxLength(45),
                TextInput::make('port')
                    ->label(__('Cổng'))
                    ->numeric()
                    ->required()
                    ->default(9100)
                    ->minValue(1)
                    ->maxValue(65535),
                TextInput::make('timeout')
                    ->label(__('Timeout (giây)'))
                    ->numeric()
                    ->required()
                    ->default(3)
                    ->minValue(1)
                    ->maxValue(30),
                Toggle::make('is_active')
                    ->label(__('Hoạt động'))
                    ->default(true),
            ]);
    }
}
