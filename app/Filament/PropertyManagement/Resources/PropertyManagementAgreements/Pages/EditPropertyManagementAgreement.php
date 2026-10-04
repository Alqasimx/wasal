<?php

namespace App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\PropertyManagementAgreementResource;
use App\Models\PropertyManagementAgreement;
use App\Services\PropertyManagementAgreementService;

class EditPropertyManagementAgreement extends AuditedEditRecord
{
    protected static string $resource = PropertyManagementAgreementResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? $this->record->status) !== PropertyManagementAgreement::STATUS_ENDED) {
            app(PropertyManagementAgreementService::class)->assertNoOverlap(
                propertyId: (int) $data['property_id'],
                startsAt: $data['starts_at'],
                endsAt: $data['ends_at'] ?? null,
                ignoreAgreementId: (int) $this->record->getKey(),
            );
        }

        return $data;
    }
}
