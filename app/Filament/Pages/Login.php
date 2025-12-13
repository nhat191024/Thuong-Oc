<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Login as BaseLogin;

use Filament\Schemas\Components\Component;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    /**
     * Tùy chỉnh để sử dụng 'username' thay vì 'email'
     *
     * @return array<string, string>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('Tên đăng nhập'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
