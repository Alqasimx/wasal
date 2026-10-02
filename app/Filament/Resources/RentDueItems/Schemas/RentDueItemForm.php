<?php

namespace App\Filament\Resources\RentDueItems\Schemas;

use App\Models\RentDueItem;
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

                    return '#'.$record->id.' — '.$tenant.' — '.$property.' / '.$unit;
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
                ->minValue(0)
                ->required(),

            Select::make('currency_id')
                ->label('العملة')
                ->relationship('currency', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('paid_amount')
                ->label('المبلغ المسدد')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->required(),

            Select::make('status')
                ->label('الحالة')
                ->options([
                    RentDueItem::STATUS_DUE => 'مستحق',
                    RentDueItem::STATUS_PARTIAL => 'مسدد جزئيًا',
                    RentDueItem::STATUS_PAID => 'مسدد',
                    RentDueItem::STATUS_OVERDUE => 'متأخر',
                    RentDueItem::STATUS_CANCELLED => 'ملغي',
                ])
                ->default(RentDueItem::STATUS_DUE)
                ->required(),
        ]);
    }
}
