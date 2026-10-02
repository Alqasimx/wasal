<?php

// app/Filament/Resources/Districts/Pages/CreateDistrict.php

namespace App\Filament\Resources\Districts\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Districts\DistrictResource;

class CreateDistrict extends AuditedCreateRecord
{
    protected static string $resource = DistrictResource::class;
}
