<?php

namespace App\Filament\PropertyManagement\Resources\PropertyInspections\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyInspections\PropertyInspectionResource;
use App\Services\InspectionWorkflowService;

class EditPropertyInspection extends AuditedEditRecord
{
    protected static string $resource = PropertyInspectionResource::class;

    protected function afterSave(): void
    {
        app(InspectionWorkflowService::class)->syncTask($this->record->fresh());
    }
}
