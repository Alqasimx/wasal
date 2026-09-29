<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

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
                    ->maxLength(255),

                TextInput::make('whatsapp_phone')
                    ->label('رقم واتساب')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(
                        fn (?string $state): ?string =>
                            filled($state) ? Hash::make($state) : null
                    )
                    ->dehydrated(
                        fn (?string $state): bool => filled($state)
                    )
                    ->minLength(8),

                Select::make('roles')
                    ->label('الأدوار')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TextInput::make('status')
                    ->label('الحالة')
                    ->required()
                    ->default('active')
                    ->maxLength(50),

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
                    ->relationship('preferredCurrency', 'name_ar')
                    ->searchable()
                    ->preload(),

                Select::make('preferred_city_id')
                    ->label('المدينة المفضلة')
                    ->relationship('preferredCity', 'name_ar')
                    ->searchable()
                    ->preload(),
            ]);
    }
}