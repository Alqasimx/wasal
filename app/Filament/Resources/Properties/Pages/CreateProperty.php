<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Properties\PropertyResource;

class CreateProperty extends AuditedCreateRecord
{
    protected static string $resource = PropertyResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();
        return $data;
    }
}
