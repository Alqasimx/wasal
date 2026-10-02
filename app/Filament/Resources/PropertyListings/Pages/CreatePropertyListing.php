<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyListings\PropertyListingResource;

class CreatePropertyListing extends AuditedCreateRecord
{
    protected static string $resource = PropertyListingResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();
        return $data;
    }
}
