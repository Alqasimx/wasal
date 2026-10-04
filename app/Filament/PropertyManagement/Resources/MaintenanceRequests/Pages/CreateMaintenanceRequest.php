<?php

namespace App\Filament\PropertyManagement\Resources\MaintenanceRequests\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\MaintenanceRequests\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceWorkflowService;

class CreateMaintenanceRequest extends AuditedCreateRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        if (($data['status'] ?? null) === MaintenanceRequest::STATUS_IN_PROGRESS) {
            $data['started_at'] = now();
        }

        if (($data['status'] ?? null) === MaintenanceRequest::STATUS_COMPLETED) {
            $data['completed_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(MaintenanceWorkflowService::class)->syncTask($this->record);
    }
}
