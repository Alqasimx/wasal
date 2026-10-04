<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;

class EditPropertyServiceSchedule extends AuditedEditRecord
{
    protected static string $resource = PropertyServiceScheduleResource::class;
}
