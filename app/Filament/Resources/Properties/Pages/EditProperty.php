<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Properties\PropertyResource;

class EditProperty extends AuditedEditRecord
{
    protected static string $resource = PropertyResource::class;
}
