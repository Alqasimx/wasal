<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $yemen = Country::updateOrCreate(
            ['code' => 'YE'],
            [
                'name_ar' => 'اليمن',
                'name_en' => 'Yemen',
                'is_active' => true,
            ]
        );

        $adenGovernorate = Governorate::updateOrCreate(
            [
                'country_id' => $yemen->id,
                'name_ar' => 'عدن',
            ],
            [
                'code' => 'ADEN',
                'name_en' => 'Aden',
                'is_active' => true,
            ]
        );

        City::updateOrCreate(
            [
                'governorate_id' => $adenGovernorate->id,
                'name_ar' => 'عدن',
            ],
            [
                'code' => 'ADEN-CITY',
                'name_en' => 'Aden',
                'is_active' => true,
            ]
        );
    }
}