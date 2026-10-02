<?php

// app/Filament/Resources/Districts/Pages/EditDistrict.php

namespace App\Filament\Resources\Districts\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Districts\DistrictResource;

class EditDistrict extends AuditedEditRecord
{
    protected static string $resource = DistrictResource::class;
}
