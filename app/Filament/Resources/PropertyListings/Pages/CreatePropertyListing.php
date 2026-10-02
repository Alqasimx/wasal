<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Models\PropertyListing;

class CreatePropertyListing extends AuditedCreateRecord
{
    protected static string $resource = PropertyListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        if (
            ($data['status'] ?? PropertyListing::STATUS_DRAFT) === PropertyListing::STATUS_PUBLISHED
            && empty($data['published_at'])
        ) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
