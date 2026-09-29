<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('رقم السجل'),

                TextEntry::make('actor.name')
                    ->label('المستخدم')
                    ->placeholder('النظام'),

                TextEntry::make('action')
                    ->label('العملية'),

                TextEntry::make('entity_type')
                    ->label('نوع السجل'),

                TextEntry::make('entity_id')
                    ->label('رقم السجل المرتبط')
                    ->placeholder('—'),

                TextEntry::make('ip_address')
                    ->label('عنوان IP')
                    ->placeholder('—'),

                TextEntry::make('user_agent')
                    ->label('المتصفح / الجهاز')
                    ->placeholder('—'),

                TextEntry::make('old_values_json')
                    ->label('القيم السابقة')
                    ->formatStateUsing(
                        fn ($state): string =>
                            is_array($state)
                                ? json_encode(
                                    $state,
                                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                                )
                                : (string) ($state ?? '—')
                    ),

                TextEntry::make('new_values_json')
                    ->label('القيم الجديدة')
                    ->formatStateUsing(
                        fn ($state): string =>
                            is_array($state)
                                ? json_encode(
                                    $state,
                                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                                )
                                : (string) ($state ?? '—')
                    ),

                TextEntry::make('created_at')
                    ->label('تاريخ العملية')
                    ->dateTime(),
            ]);
    }
}