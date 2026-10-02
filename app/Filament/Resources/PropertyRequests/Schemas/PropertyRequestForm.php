<?php
namespace App\Filament\Resources\PropertyRequests\Schemas;
use App\Models\PropertyRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
class PropertyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label('العميل')->relationship('user','name')->searchable()->preload(),
            Select::make('source')->label('المصدر')->options(['web'=>'الموقع','phone'=>'هاتف','whatsapp'=>'واتساب','office'=>'المكتب','admin'=>'الإدارة'])->required()->default('admin'),
            Select::make('purpose')->label('الغرض')->options(['sale'=>'شراء','rent'=>'إيجار'])->required(),
            Select::make('property_type_id')->label('نوع العقار')->relationship('propertyType','name_ar')->searchable()->preload(),
            Select::make('city_id')->label('المدينة')->relationship('city','name_ar')->searchable()->preload(),
            Select::make('district_id')->label('المديرية')->relationship('district','name_ar')->searchable()->preload(),
            Select::make('neighborhood_id')->label('الحي')->relationship('neighborhood','name_ar')->searchable()->preload(),
            TextInput::make('min_price')->label('الحد الأدنى للميزانية')->numeric(),
            TextInput::make('max_price')->label('الحد الأعلى للميزانية')->numeric(),
            Select::make('currency_id')->label('العملة')->relationship('currency','name_ar')->searchable()->preload(),
            Toggle::make('wants_field_search')->label('يحتاج بحثًا ميدانيًا')->default(false),
            Select::make('assigned_to_user_id')->label('الموظف المكلف')->relationship('assignedTo','name')->searchable()->preload(),
            Select::make('status')->label('الحالة')->options([
                PropertyRequest::STATUS_NEW=>'جديد', PropertyRequest::STATUS_CONTACTED=>'تم التواصل', PropertyRequest::STATUS_SEARCHING=>'جارٍ البحث', PropertyRequest::STATUS_MATCHED=>'تم العثور على خيارات', PropertyRequest::STATUS_COMPLETED=>'مكتمل', PropertyRequest::STATUS_CANCELLED=>'ملغى',
            ])->required()->default(PropertyRequest::STATUS_NEW),
            Textarea::make('notes')->label('ملاحظات')->rows(4),
        ]);
    }
}
