<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Models\RentDueItem;
use App\Services\RentDueScheduleService;
use Illuminate\Validation\ValidationException;

class EditRentDueItem extends AuditedEditRecord
{
    protected static string $resource = RentDueItemResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (RentDueItem::query()
            ->where('tenancy_id', $data['tenancy_id'])
            ->whereDate('due_date', $data['due_date'])
            ->where('id', '!=', $this->record->getKey())
            ->exists()) {
            throw ValidationException::withMessages([
                'due_date' => 'يوجد استحقاق آخر لهذا العقد في التاريخ نفسه.',
            ]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(RentDueScheduleService::class)
            ->syncStatuses($this->record->tenancy);
    }
}
