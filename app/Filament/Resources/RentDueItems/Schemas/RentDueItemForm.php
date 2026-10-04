<?php

namespace App\Filament\Resources\RentDueItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RentDueItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tenancy_id')
                ->label('عقد الإيجار')
                ->relationship('tenancy', 'id')
                ->getOptionLabelFromRecordUsing(function ($record): string {
                    $tenant = $record->tenant?->name ?? 'مستأجر';
                    $property = $record->unit?->property?->internal_code ?? 'عقار';
                    $unit = $record->unit?->code ?? 'وحدة';
                    $contract = $record->contract_number ?: '#'.$record->id;

                    return $contract.' — '.$tenant.' — '.$property.' / '.$unit;
                })
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('due_date')
                ->label('تاريخ الاستحقاق')
                ->required(),

            TextInput::make('amount')
                ->label('المبلغ المستحق')
                ->numeric()
                ->minValue(0.01)
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('paid_amount')
                ->label('المبلغ المسدد')
                ->disabled()
                ->dehydrated(false)
                ->helperText('يُحتسب تلقائيًا من التحصيلات المسجلة.'),

            TextInput::make('status')
                ->label('الحالة')
                ->disabled()
                ->dehydrated(false)
                ->helperText('تتحدث تلقائيًا حسب تاريخ الاستحقاق والتحصيلات.'),
        ]);
    }
}
