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
                        TextInput::make('app_name')
                            ->label(__(__('Tên ứng dụng')))
                            ->columnSpanFull()
                            ->required(),

                        FileUpload::make('app_logo')
                            ->label(__(__('Logo ứng dụng')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->formatStateUsing(fn($state) => $state ? str_replace('storage/', '', $state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                if (filled($state)) {
                                    if (str_starts_with($state, 'images/')) {
                                        return $state;
                                    }
                                    return 'storage/' . $state;
                                }
                                return $record?->app_logo ?? null;
                            }),

                        FileUpload::make('app_favicon')
                            ->label(__(__('Favicon ứng dụng')))
                            ->image()
                            ->directory('uploads/app')
                            ->disk('public')
                            ->visibility('public')
                            ->formatStateUsing(fn($state) => $state ? str_replace('storage/', '', $state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(function ($state, $record) {
                                if (filled($state)) {
                                    if (str_starts_with($state, 'images/')) {
                                        return $state;
                                    }
                                    return 'storage/' . $state;
                                }
                                return $record?->app_favicon ?? null;
                            }),
                    ]),
            ]);
    }
}
