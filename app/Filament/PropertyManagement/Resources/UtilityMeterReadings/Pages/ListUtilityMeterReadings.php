<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeterReadings\Pages;

use App\Filament\PropertyManagement\Resources\UtilityMeterReadings\UtilityMeterReadingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUtilityMeterReadings extends ListRecords
{
    protected static string $resource = UtilityMeterReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('تسجيل قراءة')];
    }
}
