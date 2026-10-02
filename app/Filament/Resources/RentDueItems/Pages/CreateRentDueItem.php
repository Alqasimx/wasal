<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\RentDueItems\RentDueItemResource;

class CreateRentDueItem extends AuditedCreateRecord
{
    protected static string $resource = RentDueItemResource::class;
}
