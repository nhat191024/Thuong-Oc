<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Enums\PayStatus;
use App\Enums\PaymentMethods;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('table_id')
                    ->required(),
                TextInput::make('branch_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                DateTimePicker::make('time_in')
                    ->required(),
                DateTimePicker::make('time_out'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->numeric(),
                TextInput::make('final_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('payment_method')
                    ->options(PaymentMethods::class),
                Select::make('pay_status')
                    ->options(PayStatus::class)
                    ->default('unpaid')
                    ->required(),
            ]);
    }
}
