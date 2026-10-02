<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Tasks\TaskResource;

class EditTask extends AuditedEditRecord
{
    protected static string $resource = TaskResource::class;
}
