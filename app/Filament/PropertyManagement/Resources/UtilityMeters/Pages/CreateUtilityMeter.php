<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeters\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\UtilityMeters\UtilityMeterResource;

class CreateUtilityMeter extends AuditedCreateRecord
{
    protected static string $resource = UtilityMeterResource::class;
}
