<?php

// app/Filament/Resources/Governorates/Pages/EditGovernorate.php

namespace App\Filament\Resources\Governorates\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Governorates\GovernorateResource;

class EditGovernorate extends AuditedEditRecord
{
    protected static string $resource = GovernorateResource::class;
}
