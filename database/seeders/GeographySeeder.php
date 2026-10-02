<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Governorate;
use App\Models\Neighborhood;
use App\Models\Street;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $yemen = Country::updateOrCreate(
            ['code' => 'YE'],
            ['name_ar' => 'اليمن', 'name_en' => 'Yemen', 'is_active' => true]
        );

        $adenGovernorate = Governorate::updateOrCreate(
            ['country_id' => $yemen->id, 'name_ar' => 'عدن'],
            ['code' => 'ADEN', 'name_en' => 'Aden', 'is_active' => true]
        );

        $city = City::updateOrCreate(
            ['governorate_id' => $adenGovernorate->id, 'name_ar' => 'عدن'],
            ['code' => 'ADEN-CITY', 'name_en' => 'Aden', 'is_active' => true]
        );

        $districts = [
            ['code' => 'AD-BRQ', 'name_ar' => 'البريقة', 'name_en' => 'Al Buraiqeh', 'streets' => ['الطريق الرئيسي للبريقة', 'شارع صلاح الدين']],
            ['code' => 'AD-DAR', 'name_ar' => 'دار سعد', 'name_en' => 'Dar Sad', 'streets' => ['الطريق الرئيسي لدار سعد', 'شارع دار سعد']],
            ['code' => 'AD-SHA', 'name_ar' => 'الشيخ عثمان', 'name_en' => 'Ash Shaikh Outhman', 'streets' => ['شارع الشيخ عثمان الرئيسي', 'شارع عبدالقوي']],
            ['code' => 'AD-MAN', 'name_ar' => 'المنصورة', 'name_en' => 'Al Mansura', 'streets' => ['شارع التسعين', 'شارع الخمسين', 'شارع المنصورة الرئيسي']],
            ['code' => 'AD-KHO', 'name_ar' => 'خور مكسر', 'name_en' => 'Khur Maksar', 'streets' => ['شارع المطار', 'شارع الجامعة', 'شارع ساحل أبين']],
            ['code' => 'AD-MUA', 'name_ar' => 'المعلا', 'name_en' => 'Al Mualla', 'streets' => ['شارع المعلا الرئيسي', 'شارع مدرم', 'شارع الميناء']],
            ['code' => 'AD-KRA', 'name_ar' => 'كريتر', 'name_en' => 'Crater', 'streets' => ['شارع الملكة أروى', 'شارع أروى', 'شارع الميدان']],
            ['code' => 'AD-TAW', 'name_ar' => 'التواهي', 'name_en' => 'Attawahi', 'streets' => ['شارع التواهي الرئيسي', 'شارع الهلال', 'شارع الميناء']],
        ];

        foreach ($districts as $districtData) {
            $district = District::updateOrCreate(
                ['city_id' => $city->id, 'name_ar' => $districtData['name_ar']],
                ['code' => $districtData['code'], 'name_en' => $districtData['name_en'], 'is_active' => true]
            );

            Neighborhood::updateOrCreate(
                ['district_id' => $district->id, 'name_ar' => $districtData['name_ar']],
                ['code' => $districtData['code'].'-CENTER', 'name_en' => $districtData['name_en'], 'is_active' => true]
            );

            foreach ($districtData['streets'] as $sort => $streetName) {
                Street::updateOrCreate(
                    ['city_id' => $city->id, 'district_id' => $district->id, 'name_ar' => $streetName],
                    ['name_en' => null, 'is_active' => true, 'sort_order' => $sort + 1]
                );
            }
        }
    }
}
