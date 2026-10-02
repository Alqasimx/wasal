<?php

namespace App\Filament\Resources\PropertyListings\Schemas;

use App\Models\PropertyListing;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->label('العقار')->relationship('property', 'internal_code')->searchable()->preload()->required(),
            TextInput::make('listing_number')->label('رقم الإعلان')->required()->unique(ignoreRecord: true),
            Select::make('purpose')->label('الغرض')->options(['sale' => 'بيع', 'rent' => 'إيجار'])->required(),
            TextInput::make('price')->label('السعر')->numeric()->required(),
            Select::make('currency_id')->label('العملة')->relationship('currency', 'name_ar')->searchable()->preload()->required(),
            TextInput::make('price_period')->label('دورية السعر')->placeholder('شهري / سنوي'),
            TextInput::make('public_title')->label('عنوان العرض')->required(),
            Textarea::make('public_description')->label('وصف العرض')->rows(5),
            Select::make('status')->label('حالة الإعلان')->options([
                PropertyListing::STATUS_DRAFT => 'مسودة',
                PropertyListing::STATUS_PENDING_REVIEW => 'قيد المراجعة',
                PropertyListing::STATUS_CHANGES_REQUESTED => 'مطلوب تعديل',
                PropertyListing::STATUS_APPROVED => 'معتمد',
                PropertyListing::STATUS_PUBLISHED => 'منشور',
                PropertyListing::STATUS_PAUSED => 'موقوف',
                PropertyListing::STATUS_REJECTED => 'مرفوض',
                PropertyListing::STATUS_EXPIRED => 'منتهي',
                PropertyListing::STATUS_SOLD => 'تم البيع',
                PropertyListing::STATUS_RENTED => 'تم التأجير',
            ])->required()->default(PropertyListing::STATUS_DRAFT),
            DateTimePicker::make('published_at')->label('تاريخ النشر')->seconds(false),
            DateTimePicker::make('expires_at')->label('تاريخ الانتهاء')->seconds(false),
            Toggle::make('share_enabled')->label('السماح بالمشاركة')->default(true),
            TextInput::make('share_token')->label('رمز المشاركة')->disabled()->dehydrated(false),
        ]);
    }
}
