<?php

// app/Filament/Resources/Countries/Pages/EditCountry.php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Countries\CountryResource;

class EditCountry extends AuditedEditRecord
{
    protected static string $resource = CountryResource::class;
}
