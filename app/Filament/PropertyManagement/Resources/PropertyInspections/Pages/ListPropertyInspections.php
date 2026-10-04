<?php

namespace App\Filament\PropertyManagement\Resources\PropertyInspections\Pages;

use App\Filament\PropertyManagement\Resources\PropertyInspections\PropertyInspectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyInspections extends ListRecords
{
    protected static string $resource = PropertyInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('جدولة معاينة')];
    }
}
