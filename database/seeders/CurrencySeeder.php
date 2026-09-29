<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'YER_NEW',
                'iso_code' => 'YER',
                'name_ar' => 'الريال اليمني الجديد',
                'name_en' => 'Yemeni Rial - New Notes',
                'symbol' => '﷼',
                'is_active' => true,
                'exchange_rate' => null,
            ],
            [
                'code' => 'YER_OLD',
                'iso_code' => 'YER',
                'name_ar' => 'الريال اليمني القديم',
                'name_en' => 'Yemeni Rial - Old Notes',
                'symbol' => '﷼',
                'is_active' => true,
                'exchange_rate' => null,
            ],
            [
                'code' => 'USD',
                'iso_code' => 'USD',
                'name_ar' => 'الدولار الأمريكي',
                'name_en' => 'US Dollar',
                'symbol' => '$',
                'is_active' => true,
                'exchange_rate' => null,
            ],
            [
                'code' => 'SAR',
                'iso_code' => 'SAR',
                'name_ar' => 'الريال السعودي',
                'name_en' => 'Saudi Riyal',
                'symbol' => 'SAR',
                'is_active' => true,
                'exchange_rate' => null,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}