<?php

namespace App\Filament\PropertyManagement\Resources\PropertyInspections\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyInspections\PropertyInspectionResource;
use App\Services\InspectionWorkflowService;

class CreatePropertyInspection extends AuditedCreateRecord
{
    protected static string $resource = PropertyInspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(InspectionWorkflowService::class)->syncTask($this->record->fresh());
    }
}
