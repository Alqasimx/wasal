<?php

namespace App\Filament\Resources\Tenancies\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Tenancies\TenancyResource;
use App\Models\Tenancy;
use App\Services\RentDueScheduleService;
use App\Services\TenancyService;

class CreateTenancy extends AuditedCreateRecord
{
    protected static string $resource = TenancyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? Tenancy::STATUS_ACTIVE) === Tenancy::STATUS_ACTIVE) {
            app(TenancyService::class)->assertUnitIsAvailable(
                propertyUnitId: (int) $data['property_unit_id'],
                startsAt: $data['starts_at'],
                endsAt: $data['ends_at'] ?? null,
            );
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(RentDueScheduleService::class)
            ->generateForTenancy($this->record->fresh());
    }
}
