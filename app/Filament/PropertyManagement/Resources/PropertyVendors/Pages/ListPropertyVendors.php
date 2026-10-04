<?php

namespace App\Filament\PropertyManagement\Resources\PropertyVendors\Pages;

use App\Filament\PropertyManagement\Resources\PropertyVendors\PropertyVendorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyVendors extends ListRecords
{
    protected static string $resource = PropertyVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة مورد / فني')];
    }
}
