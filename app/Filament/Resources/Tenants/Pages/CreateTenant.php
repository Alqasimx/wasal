<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Tenants\TenantResource;

class CreateTenant extends AuditedCreateRecord
{
    protected static string $resource = TenantResource::class;
}
