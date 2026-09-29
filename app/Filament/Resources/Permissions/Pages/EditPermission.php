<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Services\AuditService;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\PermissionRegistrar;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected array $auditOldValues = [];

    private const CORE_PERMISSIONS = [
        'users.view',
        'users.manage',
        'roles.view',
        'roles.manage',
        'permissions.view',
        'permissions.manage',
        'geography.view',
        'geography.manage',
        'currencies.view',
        'currencies.manage',
        'banks.view',
        'banks.manage',
        'settings.view',
        'settings.manage',
        'audit_logs.view',
    ];

    protected function beforeSave(): void
    {
        $this->auditOldValues = [
            'name' => $this->record->name,
            'guard_name' => $this->record->guard_name,
        ];

        if (
            in_array(
                $this->record->name,
                self::CORE_PERMISSIONS,
                true
            )
        ) {
            $this->data['name'] = $this->record->name;
            $this->data['guard_name'] = $this->record->guard_name;
        }
    }

    protected function afterSave(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        app(AuditService::class)->forModel(
            action: 'permission.updated',
            model: $this->record,
            oldValues: $this->auditOldValues,
            newValues: [
                'name' => $this->record->name,
                'guard_name' => $this->record->guard_name,
            ],
            actor: auth()->user(),
            request: request()
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}