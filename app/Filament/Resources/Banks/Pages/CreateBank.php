<?php

namespace App\Filament\Resources\Banks\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Banks\BankResource;

class CreateBank extends AuditedCreateRecord
{
    protected static string $resource = BankResource::class;
}
