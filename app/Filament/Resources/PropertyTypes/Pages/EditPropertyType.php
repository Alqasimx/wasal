<?php

namespace App\Filament\Resources\PropertyTypes\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyTypes\PropertyTypeResource;

class EditPropertyType extends AuditedEditRecord
{
    protected static string $resource = PropertyTypeResource::class;
}
