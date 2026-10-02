<?php

// app/Filament/Resources/Neighborhoods/Pages/CreateNeighborhood.php

namespace App\Filament\Resources\Neighborhoods\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Neighborhoods\NeighborhoodResource;

class CreateNeighborhood extends AuditedCreateRecord
{
    protected static string $resource = NeighborhoodResource::class;
}
