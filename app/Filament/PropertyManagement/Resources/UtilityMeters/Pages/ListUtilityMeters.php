<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeters\Pages;

use App\Filament\PropertyManagement\Resources\UtilityMeters\UtilityMeterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUtilityMeters extends ListRecords
{
    protected static string $resource = UtilityMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة عداد')];
    }
}
