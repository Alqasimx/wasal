<?php

namespace App\Services;

use App\Models\PropertyManagementAgreement;
use Illuminate\Validation\ValidationException;

class PropertyManagementAgreementService
{
    public function assertNoOverlap(
        int $propertyId,
        string $startsAt,
        ?string $endsAt = null,
        ?int $ignoreAgreementId = null,
    ): void {
        $query = PropertyManagementAgreement::query()
            ->where('property_id', $propertyId)
            ->whereIn('status', [
                PropertyManagementAgreement::STATUS_ACTIVE,
                PropertyManagementAgreement::STATUS_PAUSED,
            ])
            ->whereDate('starts_at', '<=', $endsAt ?? '9999-12-31')
            ->where(function ($query) use ($startsAt): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $startsAt);
            });

        if ($ignoreAgreementId !== null) {
            $query->where('id', '!=', $ignoreAgreementId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'property_id' => 'يوجد بالفعل اتفاق إدارة متداخل لهذا العقار خلال المدة المحددة.',
            ]);
        }
    }
}
