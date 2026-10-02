<?php

// app/Filament/Resources/Countries/Pages/CreateCountry.php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Countries\CountryResource;

class CreateCountry extends AuditedCreateRecord
{
    protected static string $resource = CountryResource::class;
}
