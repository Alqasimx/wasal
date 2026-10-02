<?php

namespace App\Filament\Resources\Banks\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Banks\BankResource;

class EditBank extends AuditedEditRecord
{
    protected static string $resource = BankResource::class;
}
