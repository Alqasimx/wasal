<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    private const CORE_PERMISSIONS = [
        'users.view',
        'users.manage',
        'roles.view',
        'roles.manage',
        'permissions.view',
        'permissions.manage',
        'geography.view',
        'geography.manage',
        'currencies.view',
        'currencies.manage',
        'banks.view',
        'banks.manage',
        'settings.view',
        'settings.manage',
        'audit_logs.view',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الصلاحية')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record !== null &&
                            in_array(
                                $record->name,
                                self::CORE_PERMISSIONS,
                                true
                            )
                    ),

                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->required()
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record !== null &&
                            in_array(
                                $record->name,
                                self::CORE_PERMISSIONS,
                                true
                            )
                    ),
            ]);
    }
}