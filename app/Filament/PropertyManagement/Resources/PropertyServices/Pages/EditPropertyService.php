<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServices\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyServices\PropertyServiceResource;

class EditPropertyService extends AuditedEditRecord
{
    protected static string $resource = PropertyServiceResource::class;
}
