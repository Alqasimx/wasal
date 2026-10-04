<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\RentDueItems\RentDueItemResource;
use App\Models\RentDueItem;
use App\Services\RentDueScheduleService;
use Illuminate\Validation\ValidationException;

class CreateRentDueItem extends AuditedCreateRecord
{
    protected static string $resource = RentDueItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (RentDueItem::query()
            ->where('tenancy_id', $data['tenancy_id'])
            ->whereDate('due_date', $data['due_date'])
            ->exists()) {
            throw ValidationException::withMessages([
                'due_date' => 'يوجد استحقاق لهذا العقد في التاريخ نفسه.',
            ]);
        }

        $data['paid_amount'] = 0;
        $data['status'] = RentDueItem::STATUS_DUE;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(RentDueScheduleService::class)
            ->syncStatuses($this->record->tenancy);
    }
}
