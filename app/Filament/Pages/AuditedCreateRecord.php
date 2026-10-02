<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuditsFilamentRecordChanges;
use Filament\Resources\Pages\CreateRecord;

abstract class AuditedCreateRecord extends CreateRecord
{
    use AuditsFilamentRecordChanges;

    protected function afterCreate(): void
    {
        $this->writeCreatedAuditLog();
    }
}