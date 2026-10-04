<?php

namespace App\Filament\PropertyManagement\Resources\RentPayments\Schemas;

use App\Models\RentPayment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RentPaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('receipt_number')
                ->label('رقم سند القبض')
                ->copyable(),

            TextEntry::make('paid_at')
                ->label('تاريخ التحصيل')
                ->dateTime('Y-m-d H:i'),

            TextEntry::make('dueItem.tenancy.tenant.name')
                ->label('المستأجر'),

            TextEntry::make('dueItem.tenancy.contract_number')
                ->label('رقم العقد')
                ->placeholder('—'),

            TextEntry::make('dueItem.tenancy.unit.property.internal_code')
                ->label('العقار'),

            TextEntry::make('dueItem.tenancy.unit.code')
                ->label('الوحدة'),

            TextEntry::make('amount')
                ->label('المبلغ')
                ->state(fn (RentPayment $record): string =>
                    number_format((float) $record->amount, 2).' '.($record->currency?->code ?? '')),

            TextEntry::make('payment_method')
                ->label('طريقة الدفع')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'bank_transfer' => 'تحويل بنكي',
                    'mobile_wallet' => 'محفظة إلكترونية',
                    'cheque' => 'شيك',
                    'other' => 'أخرى',
                    default => 'نقدي',
                }),

            TextEntry::make('bank.name')
                ->label('الحساب البنكي')
                ->placeholder('—'),

            TextEntry::make('reference_number')
                ->label('مرجع الدفع')
                ->placeholder('—'),

            TextEntry::make('recorder.name')
                ->label('سجلها الموظف')
                ->placeholder('—'),

            TextEntry::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn (string $state): string =>
                    $state === RentPayment::STATUS_VOIDED ? 'ملغاة' : 'مرحّلة'),

            TextEntry::make('notes')
                ->label('ملاحظات')
                ->placeholder('—')
                ->columnSpanFull(),

            TextEntry::make('void_reason')
                ->label('سبب الإلغاء')
                ->placeholder('—')
                ->visible(fn (RentPayment $record): bool =>
                    $record->status === RentPayment::STATUS_VOIDED)
                ->columnSpanFull(),
        ]);
    }
}
