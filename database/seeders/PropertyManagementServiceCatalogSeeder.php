<?php

namespace Database\Seeders;

use App\Models\PropertyService;
use Illuminate\Database\Seeder;

class PropertyManagementServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['ELEC', 'صيانة الكهرباء', 'Electrical maintenance', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_ONCE],
            ['PLUMB', 'السباكة', 'Plumbing', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_ONCE],
            ['AC', 'صيانة التكييف', 'Air-conditioning maintenance', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_SEMIANNUAL],
            ['SOLAR-CLEAN', 'تنظيف الألواح الشمسية', 'Solar panel cleaning', PropertyService::CATEGORY_CLEANING, PropertyService::FREQUENCY_MONTHLY],
            ['SOLAR-MAINT', 'صيانة منظومة الطاقة الشمسية', 'Solar system maintenance', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_QUARTERLY],
            ['TANK-CLEAN', 'تنظيف خزانات المياه', 'Water tank cleaning', PropertyService::CATEGORY_CLEANING, PropertyService::FREQUENCY_SEMIANNUAL],
            ['PUMP', 'فحص وصيانة المضخات', 'Pump inspection and maintenance', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_QUARTERLY],
            ['GENERAL-CLEAN', 'نظافة عامة', 'General cleaning', PropertyService::CATEGORY_CLEANING, PropertyService::FREQUENCY_MONTHLY],
            ['PEST', 'مكافحة الحشرات', 'Pest control', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_QUARTERLY],
            ['PAINT', 'الدهان', 'Painting', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_ONCE],
            ['CARPENTRY', 'النجارة', 'Carpentry', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_ONCE],
            ['ELEVATOR', 'صيانة المصاعد', 'Elevator maintenance', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_MONTHLY],
            ['SECURITY', 'الكاميرات وأنظمة الأمن', 'Security and CCTV', PropertyService::CATEGORY_MAINTENANCE, PropertyService::FREQUENCY_QUARTERLY],
            ['NETWORK', 'الشبكات والإنترنت', 'Network and internet', PropertyService::CATEGORY_UTILITY, PropertyService::FREQUENCY_ONCE],
            ['INSPECTION', 'فحص دوري للعقار', 'Periodic property inspection', PropertyService::CATEGORY_INSPECTION, PropertyService::FREQUENCY_QUARTERLY],
        ];

        foreach ($services as [$code, $nameAr, $nameEn, $category, $frequency]) {
            PropertyService::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name_ar' => $nameAr,
                    'name_en' => $nameEn,
                    'category' => $category,
                    'default_frequency' => $frequency,
                    'default_interval' => 1,
                    'notify_before_minutes' => 1440,
                    'creates_task' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );
        }
    }
}
