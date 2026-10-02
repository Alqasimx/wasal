<?php

namespace App\Filament\Resources\Currencies\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Currencies\CurrencyResource;

class CreateCurrency extends AuditedCreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
