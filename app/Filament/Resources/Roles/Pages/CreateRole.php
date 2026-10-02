<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $this->record->load('permissions');

        app(AuditService::class)->forModel(
            action: 'role.created',
            model: $this->record,
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
}
