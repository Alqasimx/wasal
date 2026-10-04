<?php

namespace App\Filament\PropertyManagement\Resources\PropertyVendors\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyVendors\PropertyVendorResource;

class CreatePropertyVendor extends AuditedCreateRecord
{
    protected static string $resource = PropertyVendorResource::class;
}
