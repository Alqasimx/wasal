<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Tenants\TenantResource;

class EditTenant extends AuditedEditRecord
{
    protected static string $resource = TenantResource::class;
}
