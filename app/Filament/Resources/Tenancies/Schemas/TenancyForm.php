<?php

namespace App\Filament\Resources\Tenancies\Schemas;

use App\Models\Tenancy;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TenancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('contract_number')
                ->label('رقم العقد')
                ->disabled()
                ->dehydrated(false)
                ->placeholder('يُنشأ تلقائيًا عند الحفظ'),

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
                ->label('نهاية العقد')
                ->afterOrEqual('starts_at'),

            TextInput::make('rent_amount')
                ->label('قيمة الإيجار لكل دورة سداد')
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

            TextInput::make('due_day')
                ->label('يوم الاستحقاق')
                ->numeric()
                ->minValue(1)
                ->maxValue(31)
                ->helperText('إذا تُرك فارغًا فسيُستخدم يوم بداية العقد.'),

            TextInput::make('grace_days')
                ->label('فترة السماح بالأيام')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            TextInput::make('security_deposit')
                ->label('مبلغ التأمين')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            Toggle::make('auto_generate_dues')
                ->label('توليد الاستحقاقات تلقائيًا')
                ->default(true),

            FileUpload::make('contract_attachment_path')
                ->label('نسخة العقد')
                ->disk('public')
                ->directory('property-management/contracts')
                ->downloadable()
                ->openable(),

            Textarea::make('notes')
                ->label('ملاحظات العقد')
                ->rows(4),

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
