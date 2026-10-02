<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuditsFilamentRecordChanges;
use Filament\Resources\Pages\EditRecord;

abstract class AuditedEditRecord extends EditRecord
{
    use AuditsFilamentRecordChanges;

    protected function beforeSave(): void
    {
        $this->captureAuditOldValues();
    }

    protected function afterSave(): void
    {
        $this->writeUpdatedAuditLog();
    }
}