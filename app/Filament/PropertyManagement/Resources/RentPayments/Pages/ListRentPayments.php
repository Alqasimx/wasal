<?php

namespace App\Filament\PropertyManagement\Resources\RentPayments\Pages;

use App\Filament\PropertyManagement\Resources\RentPayments\RentPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentPayments extends ListRecords
{
    protected static string $resource = RentPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('تسجيل دفعة')];
    }
}
