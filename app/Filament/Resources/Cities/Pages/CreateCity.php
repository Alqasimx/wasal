<?php

// app/Filament/Resources/Cities/Pages/CreateCity.php

namespace App\Filament\Resources\Cities\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Cities\CityResource;

class CreateCity extends AuditedCreateRecord
{
    protected static string $resource = CityResource::class;
}
