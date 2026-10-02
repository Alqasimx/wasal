<?php

// app/Filament/Resources/Governorates/Pages/CreateGovernorate.php

namespace App\Filament\Resources\Governorates\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Governorates\GovernorateResource;

class CreateGovernorate extends AuditedCreateRecord
{
    protected static string $resource = GovernorateResource::class;
}
