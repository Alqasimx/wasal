<?php

namespace App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\PropertyDocumentResource;
use App\Models\PropertyDocument;

class CreatePropertyDocument extends AuditedCreateRecord
{
    protected static string $resource = PropertyDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        if (
            ($data['status'] ?? PropertyDocument::STATUS_ACTIVE) !== PropertyDocument::STATUS_ARCHIVED
            && ! empty($data['expires_at'])
            && now()->startOfDay()->gt($data['expires_at'])
        ) {
            $data['status'] = PropertyDocument::STATUS_EXPIRED;
        }

        return $data;
    }
}
