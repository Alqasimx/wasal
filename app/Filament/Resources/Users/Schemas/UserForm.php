<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->maxLength(30),

                TextInput::make('whatsapp_phone')
                    ->label('رقم واتساب')
                    ->tel()
                    ->maxLength(30),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->visible(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    )
                    ->dehydrated(
                        fn (string $operation): bool =>
                            $operation === 'create'
                    ),

                Select::make('roles')
                    ->label('الأدوار')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->disabled(
                        fn ($record): bool =>
                            $record?->hasRole('system_admin') ?? false
                    ),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'inactive' => 'غير نشط',
                        'suspended' => 'موقوف',
                    ])
                    ->default('active')
                    ->required()
                    ->disabled(
                        fn ($record): bool =>
                            $record?->hasRole('system_admin') ?? false
                    ),

                Select::make('preferred_language')
                    ->label('اللغة المفضلة')
                    ->options([
                        'ar' => 'العربية',
                        'en' => 'English',
                    ])
                    ->default('ar')
                    ->required(),

                Select::make('preferred_currency_id')
                    ->label('العملة المفضلة')
                    ->relationship(
                        'preferredCurrency',
                        'name_ar'
                    )
                    ->searchable()
                    ->preload(),

                Select::make('preferred_city_id')
                    ->label('المدينة المفضلة')
                    ->relationship(
                        'preferredCity',
                        'name_ar'
                    )
                    ->searchable()
                    ->preload(),
            ]);
    }
}