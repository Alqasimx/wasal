<?php

namespace App\Filament\PropertyManagement\Resources\MaintenanceRequests\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceWorkflowService;

class EditMaintenanceRequest extends AuditedEditRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            ($data['status'] ?? null) === MaintenanceRequest::STATUS_IN_PROGRESS
            && $this->record->started_at === null
        ) {
            $data['started_at'] = now();
        }

        if (
            ($data['status'] ?? null) === MaintenanceRequest::STATUS_COMPLETED
            && $this->record->completed_at === null
        ) {
            $data['completed_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(MaintenanceWorkflowService::class)->syncTask($this->record);
    }
}
