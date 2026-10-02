<?php

namespace App\Filament\Resources\PropertyTypes\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyTypes\PropertyTypeResource;

class CreatePropertyType extends AuditedCreateRecord
{
    protected static string $resource = PropertyTypeResource::class;
}
