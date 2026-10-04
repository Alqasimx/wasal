<?php

namespace App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\PropertyManagementAgreementResource;
use App\Models\PropertyManagementAgreement;
use App\Services\PropertyManagementAgreementService;

class CreatePropertyManagementAgreement extends AuditedCreateRecord
{
    protected static string $resource = PropertyManagementAgreementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? PropertyManagementAgreement::STATUS_ACTIVE) !== PropertyManagementAgreement::STATUS_ENDED) {
            app(PropertyManagementAgreementService::class)->assertNoOverlap(
                propertyId: (int) $data['property_id'],
                startsAt: $data['starts_at'],
                endsAt: $data['ends_at'] ?? null,
            );
        }

        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
