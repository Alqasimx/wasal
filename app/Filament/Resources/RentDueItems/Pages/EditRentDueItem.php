<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Services\RentDueScheduleService;

class EditRentDueItem extends AuditedEditRecord
{
    protected static string $resource = RentDueItemResource::class;

    protected function afterSave(): void
    {
        app(RentDueScheduleService::class)
            ->syncStatuses($this->record->tenancy);
    }
}
