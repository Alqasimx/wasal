<?php

namespace App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyServiceSchedules\PropertyServiceScheduleResource;

class CreatePropertyServiceSchedule extends AuditedCreateRecord
{
    protected static string $resource = PropertyServiceScheduleResource::class;
}
