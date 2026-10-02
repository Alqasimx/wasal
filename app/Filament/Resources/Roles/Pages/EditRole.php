<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Permission;
use App\Services\AuditService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected array $auditOldValues = [];

    protected function beforeSave(): void
    {
        $this->record->load('permissions');

        $this->auditOldValues = [
            'name' => $this->record->name,
            'guard_name' => $this->record->guard_name,
            'permissions' => $this->record
                ->permissions
                ->pluck('name')
                ->values()
                ->all(),
        ];

        if ($this->record->name === 'system_admin') {
            $this->data['name'] = 'system_admin';
            $this->data['guard_name'] = $this->record->guard_name;

            $this->data['permissions'] = Permission::query()
                ->pluck('id')
                ->all();
        }
    }

    protected function afterSave(): void
    {
        if ($this->record->name === 'system_admin') {
            $this->record->syncPermissions(
                Permission::query()->get()
            );
        }

        $this->record->load('permissions');

        app(AuditService::class)->forModel(
            action: 'role.updated',
            model: $this->record,
            oldValues: $this->auditOldValues,
            newValues: [
                'name' => $this->record->name,
                'guard_name' => $this->record->guard_name,
                'permissions' => $this->record
                    ->permissions
                    ->pluck('name')
                    ->values()
                    ->all(),
            ],
            actor: auth()->user(),
            request: request()
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(
                    fn (): bool =>
                        $this->record->name !== 'system_admin'
                ),
        ];
    }
}
