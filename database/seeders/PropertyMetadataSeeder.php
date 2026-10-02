<?php

namespace Database\Seeders;

use App\Models\PropertyFeature;
use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyMetadataSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'apartment', 'name_ar' => 'شقة', 'name_en' => 'Apartment', 'sort_order' => 10],
            ['slug' => 'villa', 'name_ar' => 'فيلا', 'name_en' => 'Villa', 'sort_order' => 20],
            ['slug' => 'house', 'name_ar' => 'بيت', 'name_en' => 'House', 'sort_order' => 30],
            ['slug' => 'floor', 'name_ar' => 'دور', 'name_en' => 'Floor', 'sort_order' => 40],
            ['slug' => 'land', 'name_ar' => 'أرض', 'name_en' => 'Land', 'sort_order' => 50],
            ['slug' => 'building', 'name_ar' => 'عمارة', 'name_en' => 'Building', 'sort_order' => 60],
            ['slug' => 'shop', 'name_ar' => 'محل تجاري', 'name_en' => 'Shop', 'sort_order' => 70],
            ['slug' => 'office', 'name_ar' => 'مكتب', 'name_en' => 'Office', 'sort_order' => 80],
            ['slug' => 'farm', 'name_ar' => 'مزرعة', 'name_en' => 'Farm', 'sort_order' => 90],
            ['slug' => 'warehouse', 'name_ar' => 'مستودع', 'name_en' => 'Warehouse', 'sort_order' => 100],
            ['slug' => 'chalet', 'name_ar' => 'شاليه', 'name_en' => 'Chalet', 'sort_order' => 110],
        ];

        $features = [
            ['slug' => 'bedrooms', 'key' => 'bedrooms', 'name_ar' => 'غرف النوم', 'name_en' => 'Bedrooms', 'data_type' => 'integer', 'is_filterable' => true, 'is_searchable' => true],
            ['slug' => 'bathrooms', 'key' => 'bathrooms', 'name_ar' => 'الحمامات', 'name_en' => 'Bathrooms', 'data_type' => 'integer', 'is_filterable' => true, 'is_searchable' => true],
            ['slug' => 'halls', 'key' => 'halls', 'name_ar' => 'الصالات', 'name_en' => 'Halls', 'data_type' => 'integer', 'is_filterable' => true, 'is_searchable' => true],
            ['slug' => 'furnished', 'key' => 'furnished', 'name_ar' => 'التأثيث', 'name_en' => 'Furnished', 'data_type' => 'select', 'options' => ['مفروش', 'غير مفروش', 'نصف مفروش'], 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'elevator', 'key' => 'elevator', 'name_ar' => 'مصعد', 'name_en' => 'Elevator', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'parking', 'key' => 'parking', 'name_ar' => 'موقف سيارات', 'name_en' => 'Parking', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'private_roof', 'key' => 'private_roof', 'name_ar' => 'سطح خاص', 'name_en' => 'Private Roof', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'air_conditioning', 'key' => 'air_conditioning', 'name_ar' => 'تكييف', 'name_en' => 'Air Conditioning', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'garden', 'key' => 'garden', 'name_ar' => 'حديقة', 'name_en' => 'Garden', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'kitchen', 'key' => 'kitchen', 'name_ar' => 'مطبخ مجهز', 'name_en' => 'Equipped Kitchen', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'security', 'key' => 'security', 'name_ar' => 'حراسة وأمن', 'name_en' => 'Security', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
            ['slug' => 'pool', 'key' => 'pool', 'name_ar' => 'مسبح', 'name_en' => 'Pool', 'data_type' => 'boolean', 'is_filterable' => true, 'is_searchable' => false],
        ];

        foreach ($types as $typeData) {
            $type = PropertyType::updateOrCreate(['slug' => $typeData['slug']], $typeData + ['is_active' => true]);

            foreach ($features as $sort => $featureData) {
                $feature = PropertyFeature::updateOrCreate(
                    ['slug' => $featureData['slug']],
                    $featureData + ['is_active' => true, 'sort_order' => $sort + 1]
                );

                $type->features()->syncWithoutDetaching([
                    $feature->id => ['is_required' => in_array($feature->key, ['bedrooms', 'bathrooms'], true), 'sort_order' => $sort + 1],
                ]);
            }
        }
    }
}
