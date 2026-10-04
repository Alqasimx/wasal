<?php

namespace App\Filament\Resources\Tenancies\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Models\Tenancy;
use App\Services\RentDueScheduleService;
use App\Services\TenancyService;

class EditTenancy extends AuditedEditRecord
{
    protected static string $resource = TenancyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? $this->record->status) === Tenancy::STATUS_ACTIVE) {
            app(TenancyService::class)->assertUnitIsAvailable(
                propertyUnitId: (int) $data['property_unit_id'],
                startsAt: $data['starts_at'],
                endsAt: $data['ends_at'] ?? null,
                ignoreTenancyId: (int) $this->record->getKey(),
            );
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(RentDueScheduleService::class)
            ->generateForTenancy($this->record->fresh());
    }
}
