<?php

namespace App\Filament\PropertyManagement\Resources\OwnerSettlements\Pages;

use App\Filament\PropertyManagement\Resources\OwnerSettlements\OwnerSettlementResource;
use App\Models\PropertyManagementAgreement;
use App\Services\OwnerSettlementService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CreateOwnerSettlement extends CreateRecord
{
    protected static string $resource = OwnerSettlementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(OwnerSettlementService::class)->generate(
            agreement: PropertyManagementAgreement::query()
                ->findOrFail($data['property_management_agreement_id']),
            periodStart: Carbon::parse($data['period_start']),
            periodEnd: Carbon::parse($data['period_end']),
            currencyId: (int) $data['currency_id'],
            actor: auth()->user(),
            notes: $data['notes'] ?? null,
        );
    }
}
