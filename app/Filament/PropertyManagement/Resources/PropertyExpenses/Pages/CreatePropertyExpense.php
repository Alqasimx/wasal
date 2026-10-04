<?php

namespace App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource;
use App\Models\PropertyExpense;

class CreatePropertyExpense extends AuditedCreateRecord
{
    protected static string $resource = PropertyExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();
        $data['payment_status'] = $this->resolvePaymentStatus($data);

        return $data;
    }

    private function resolvePaymentStatus(array $data): string
    {
        $amount = (float) ($data['amount'] ?? 0);
        $paid = (float) ($data['paid_amount'] ?? 0);

        return match (true) {
            $amount > 0 && $paid >= $amount => PropertyExpense::STATUS_PAID,
            $paid > 0 => PropertyExpense::STATUS_PARTIAL,
            default => PropertyExpense::STATUS_UNPAID,
        };
    }
}
