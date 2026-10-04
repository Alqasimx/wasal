<?php

namespace App\Filament\PropertyManagement\Resources\PropertyVendors\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyVendors\PropertyVendorResource;

class EditPropertyVendor extends AuditedEditRecord
{
    protected static string $resource = PropertyVendorResource::class;
}
