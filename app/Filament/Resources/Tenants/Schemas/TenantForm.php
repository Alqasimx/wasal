<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('حساب المستخدم المرتبط')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->helperText('اختياري، يمكن تسجيل مستأجر بدون حساب مستخدم.'),

            TextInput::make('name')
                ->label('اسم المستأجر')
                ->required()
                ->maxLength(255),

            TextInput::make('phone')
                ->label('رقم الهاتف')
                ->tel()
                ->required()
                ->maxLength(50),

            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(255),

            TextInput::make('identity_number')
                ->label('رقم الهوية')
                ->maxLength(100),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(4),
        ]);
    }
}
