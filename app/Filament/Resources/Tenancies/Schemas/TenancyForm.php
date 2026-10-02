<?php

namespace App\Filament\Resources\Tenancies\Schemas;

use App\Models\Tenancy;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TenancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_unit_id')
                ->label('الوحدة العقارية')
                ->relationship('unit', 'code')
                ->getOptionLabelFromRecordUsing(function ($record): string {
                    $propertyCode = $record->property?->internal_code ?? 'عقار';
                    $unitName = $record->name ? ' — '.$record->name : '';

                    return $propertyCode.' / '.$record->code.$unitName;
                })
                ->searchable(['code', 'name'])
                ->preload()
                ->required(),

            Select::make('tenant_id')
                ->label('المستأجر')
                ->relationship('tenant', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('starts_at')
                ->label('بداية العقد')
                ->required(),

            DatePicker::make('ends_at')
                ->label('نهاية العقد'),

            TextInput::make('rent_amount')
                ->label('قيمة الإيجار')
                ->numeric()
                ->minValue(0)
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('payment_frequency')
                ->label('دورية السداد')
                ->options([
                    Tenancy::FREQUENCY_MONTHLY => 'شهري',
                    Tenancy::FREQUENCY_QUARTERLY => 'كل 3 أشهر',
                    Tenancy::FREQUENCY_SEMIANNUAL => 'كل 6 أشهر',
                    Tenancy::FREQUENCY_ANNUAL => 'سنوي',
                ])
                ->default(Tenancy::FREQUENCY_MONTHLY)
                ->required(),

            Select::make('status')
                ->label('حالة العقد')
                ->options([
                    Tenancy::STATUS_ACTIVE => 'نشط',
                    Tenancy::STATUS_ENDED => 'منتهي',
                    Tenancy::STATUS_CANCELLED => 'ملغي',
                ])
                ->default(Tenancy::STATUS_ACTIVE)
                ->required(),
        ]);
    }
}
