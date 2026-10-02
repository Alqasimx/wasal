<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Currencies\CurrencyResource;

class EditCurrency extends AuditedEditRecord
{
    protected static string $resource = CurrencyResource::class;
}
