<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServices\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyServices\PropertyServiceResource;

class CreatePropertyService extends AuditedCreateRecord
{
    protected static string $resource = PropertyServiceResource::class;
}
