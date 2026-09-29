<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\AuditsFilamentRecordChanges;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use AuditsFilamentRecordChanges;

    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->writeCreatedAuditLog();
    }
}