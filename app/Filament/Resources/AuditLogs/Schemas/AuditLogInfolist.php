<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use App\Models\AuditLog;
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
                    ->label('نوع السجل')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state
                                ? class_basename($state)
                                : '—'
                    ),

                TextEntry::make('entity_id')
                    ->label('رقم السجل المرتبط')
                    ->placeholder('—'),

                TextEntry::make('ip_address')
                    ->label('عنوان IP')
                    ->placeholder('—'),

                TextEntry::make('user_agent')
                    ->label('المتصفح / الجهاز')
                    ->placeholder('—')
                    ->columnSpanFull(),

                TextEntry::make('old_values_json')
                    ->label('القيم السابقة')
                    ->state(
                        fn (AuditLog $record): string =>
                            $record->old_values_json
                                ? json_encode(
                                    $record->old_values_json,
                                    JSON_PRETTY_PRINT |
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_UNESCAPED_SLASHES
                                )
                                : 'لا توجد قيم سابقة'
                    )
                    ->columnSpanFull(),

                TextEntry::make('new_values_json')
                    ->label('القيم الجديدة')
                    ->state(
                        fn (AuditLog $record): string =>
                            $record->new_values_json
                                ? json_encode(
                                    $record->new_values_json,
                                    JSON_PRETTY_PRINT |
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_UNESCAPED_SLASHES
                                )
                                : 'لا توجد قيم جديدة'
                    )
                    ->columnSpanFull(),

                TextEntry::make('created_at')
                    ->label('تاريخ العملية')
                    ->dateTime('Y-m-d H:i:s'),
            ]);
    }
}