<?php

namespace App\Filament\PropertyManagement\Resources\RentPayments\Pages;

use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use App\Models\RentPayment;
use App\Services\RentPaymentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRentPayment extends CreateRecord
{
    protected static string $resource = RentPaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(RentPaymentService::class)->record(
            data: $data,
            actor: auth()->user(),
        );
    }
}
