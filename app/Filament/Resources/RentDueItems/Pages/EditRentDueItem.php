<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\RentDueItems\RentDueItemResource;

class EditRentDueItem extends AuditedEditRecord
{
    protected static string $resource = RentDueItemResource::class;
}
