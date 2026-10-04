<?php

namespace App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\PropertyManagement\Resources\PropertyDocuments\PropertyDocumentResource;
use App\Models\PropertyDocument;
use Illuminate\Support\Carbon;

class EditPropertyDocument extends AuditedEditRecord
{
    protected static string $resource = PropertyDocumentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) !== PropertyDocument::STATUS_ARCHIVED) {
            $data['status'] = ! empty($data['expires_at'])
                && today()->gt(Carbon::parse($data['expires_at']))
                    ? PropertyDocument::STATUS_EXPIRED
                    : PropertyDocument::STATUS_ACTIVE;
        }

        return $data;
    }
}
