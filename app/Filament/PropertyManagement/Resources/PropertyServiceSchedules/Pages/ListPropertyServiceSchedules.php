<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages;

use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyServiceSchedules extends ListRecords
{
    protected static string $resource = PropertyServiceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة جدول خدمة')];
    }
}
