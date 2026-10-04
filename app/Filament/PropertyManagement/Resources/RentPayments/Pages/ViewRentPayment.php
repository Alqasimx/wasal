<?php

namespace App\Filament\PropertyManagement\Resources\RentPayments\Pages;

use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRentPayment extends ViewRecord
{
    protected static string $resource = RentPaymentResource::class;

    public function getTitle(): string
    {
        return 'سند قبض '.$this->record->receipt_number;
    }
}
