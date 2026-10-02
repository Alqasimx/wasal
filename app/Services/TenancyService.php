<?php

namespace App\Services;

use App\Models\Tenancy;
use Illuminate\Validation\ValidationException;

class TenancyService
{
    public function assertUnitIsAvailable(
        int $propertyUnitId,
        string $startsAt,
        ?string $endsAt = null,
        ?int $ignoreTenancyId = null,
    ): void {
        $query = Tenancy::query()
            ->where('property_unit_id', $propertyUnitId)
            ->where('status', Tenancy::STATUS_ACTIVE)
            ->whereDate('starts_at', '<=', $endsAt ?? '9999-12-31')
            ->where(function ($query) use ($startsAt): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $startsAt);
            });

        if ($ignoreTenancyId !== null) {
            $query->whereKeyNot($ignoreTenancyId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'property_unit_id' => 'هذه الوحدة لديها عقد نشط متداخل مع المدة المحددة.',
            ]);
        }
    }
}
