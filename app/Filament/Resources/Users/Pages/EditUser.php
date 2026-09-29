<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\AuditsFilamentRecordChanges;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use AuditsFilamentRecordChanges;

    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        $this->captureAuditOldValues();
    }

    protected function afterSave(): void
    {
        $this->writeUpdatedAuditLog();
    }
}