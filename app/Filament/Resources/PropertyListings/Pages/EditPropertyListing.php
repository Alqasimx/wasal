<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyListings\PropertyListingResource;

class EditPropertyListing extends AuditedEditRecord
{
    protected static string $resource = PropertyListingResource::class;
}
