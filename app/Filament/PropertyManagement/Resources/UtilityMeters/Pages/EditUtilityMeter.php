<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeters\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\UtilityMeters\UtilityMeterResource;

class EditUtilityMeter extends AuditedEditRecord
{
    protected static string $resource = UtilityMeterResource::class;
}
