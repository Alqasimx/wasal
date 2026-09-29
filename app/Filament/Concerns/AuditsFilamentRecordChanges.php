<?php

namespace App\Filament\Concerns;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait AuditsFilamentRecordChanges
{
    protected array $auditOldValues = [];

    protected function auditActionPrefix(): string
    {
        return class_basename($this->record);
    }

    protected function auditExcludedFields(): array
    {
        return [
            'password',
            'remember_token',
            'code_hash',
            'token_hash',
        ];
    }

    protected function auditValues(Model $record): array
    {
        return collect($record->getAttributes())
            ->except($this->auditExcludedFields())
            ->all();
    }

    protected function captureAuditOldValues(): void
    {
        $this->auditOldValues = $this->auditValues($this->record);
    }

    protected function writeCreatedAuditLog(): void
    {
        app(AuditService::class)->forModel(
            action: strtolower($this->auditActionPrefix()) . '.created',
            model: $this->record,
            newValues: $this->auditValues($this->record),
            actor: auth()->user(),
            request: request()
        );
    }

    protected function writeUpdatedAuditLog(): void
    {
        $this->record->refresh();

        $newValues = $this->auditValues($this->record);

        $changedOld = [];
        $changedNew = [];

        foreach ($newValues as $key => $value) {
            $oldValue = $this->auditOldValues[$key] ?? null;

            if ($oldValue !== $value) {
                $changedOld[$key] = $oldValue;
                $changedNew[$key] = $value;
            }
        }

        if ($changedNew === []) {
            return;
        }

        app(AuditService::class)->forModel(
            action: strtolower($this->auditActionPrefix()) . '.updated',
            model: $this->record,
            oldValues: $changedOld,
            newValues: $changedNew,
            actor: auth()->user(),
            request: request()
        );
    }
}