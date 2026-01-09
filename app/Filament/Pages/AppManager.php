<?php

namespace App\Filament\Pages;

use BackedEnum;

use App\Settings\AppSettings;

use Filament\Pages\SettingsPage;
use Filament\Support\Icons\Heroicon;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

// use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class AppManager extends SettingsPage
{
    // use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string $settings = AppSettings::class;
    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Cài đặt ứng dụng');
    }

    public function getTitle(): string
    {
        return __('Cài đặt ứng dụng');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Cấu hình ứng dụng'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('point_step')
                            ->label(__('Quy đổi điểm thưởng'))
                            ->helperText(__('Số tiền quy đổi ra 1 điểm. Ví dụ: 1000 đồng = 1 điểm, đơn 10000 đồng sẽ được 10 điểm thưởng.'))
                            ->columnSpanFull()
                            ->required()
                            ->numeric(),

                        TextInput::make('app_name')
                            ->label(__(__('Tên ứng dụng')))
                            ->columnSpanFull()
                            ->required(),

                        FileUpload::make('app_logo')
                            ->label(__(__('Logo ứng dụng')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public'),

                        FileUpload::make('app_favicon')
                            ->label(__(__('Favicon ứng dụng')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
