<?php

// app/Filament/Resources/Neighborhoods/Pages/EditNeighborhood.php

namespace App\Filament\Resources\Neighborhoods\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Neighborhoods\NeighborhoodResource;

class EditNeighborhood extends AuditedEditRecord
{
    protected static string $resource = NeighborhoodResource::class;
}
