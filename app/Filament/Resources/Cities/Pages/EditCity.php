<?php

// app/Filament/Resources/Cities/Pages/EditCity.php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Cities\CityResource;

class EditCity extends AuditedEditRecord
{
    protected static string $resource = CityResource::class;
}
