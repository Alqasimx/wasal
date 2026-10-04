<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServices\Pages;

use App\Filament\PropertyManagement\Resources\PropertyServices\PropertyServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyServices extends ListRecords
{
    protected static string $resource = PropertyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة خدمة')];
    }
}
