<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الدور')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record?->name === 'system_admin'
                    ),

                TextInput::make('guard_name')
                    ->label('Guard')
                    ->default('web')
                    ->required()
                    ->maxLength(255)
                    ->disabled(
                        fn ($record): bool =>
                            $record?->name === 'system_admin'
                    ),

                Select::make('permissions')
                    ->label('الصلاحيات')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->disabled(
                        fn ($record): bool =>
                            $record?->name === 'system_admin'
                    ),
            ]);
    }
}