<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use App\Models\Role;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function afterCreate(): void
    {
        $systemAdmin = Role::query()
            ->where('name', 'system_admin')
            ->where('guard_name', $this->record->guard_name)
            ->first();

        if ($systemAdmin) {
            $systemAdmin->givePermissionTo($this->record);
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        app(AuditService::class)->forModel(
            action: 'permission.created',
            model: $this->record,
            newValues: [
                'name' => $this->record->name,
                'guard_name' => $this->record->guard_name,
            ],
            actor: auth()->user(),
            request: request()
        );
    }
}
