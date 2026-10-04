<?php

namespace App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource;
use App\Models\PropertyExpense;

class EditPropertyExpense extends AuditedEditRecord
{
    protected static string $resource = PropertyExpenseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $amount = (float) ($data['amount'] ?? 0);
        $paid = (float) ($data['paid_amount'] ?? 0);

        $data['payment_status'] = match (true) {
            $amount > 0 && $paid >= $amount => PropertyExpense::STATUS_PAID,
            $paid > 0 => PropertyExpense::STATUS_PARTIAL,
            default => PropertyExpense::STATUS_UNPAID,
        };

        return $data;
    }
}
