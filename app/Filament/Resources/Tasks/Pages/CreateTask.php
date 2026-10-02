<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Tasks\TaskResource;

class CreateTask extends AuditedCreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
