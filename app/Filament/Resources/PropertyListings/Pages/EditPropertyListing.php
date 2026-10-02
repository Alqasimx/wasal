<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Models\PropertyListing;

class EditPropertyListing extends AuditedEditRecord
{
    protected static string $resource = PropertyListingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (
            ($data['status'] ?? $this->record->status) === PropertyListing::STATUS_PUBLISHED
            && empty($data['published_at'])
        ) {
            $data['published_at'] = $this->record->published_at ?? now();
        }

        return $data;
    }
}
