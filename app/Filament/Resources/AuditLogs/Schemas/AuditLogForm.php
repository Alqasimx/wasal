<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('actor_user_id')
                    ->numeric(),
                TextInput::make('action')
                    ->required(),
                TextInput::make('entity_type')
                    ->required(),
                TextInput::make('entity_id')
                    ->numeric(),
                TextInput::make('old_values_json'),
                TextInput::make('new_values_json'),
                TextInput::make('ip_address'),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
            ]);
    }
}
