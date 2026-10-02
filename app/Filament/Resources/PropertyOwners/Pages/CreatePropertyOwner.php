<?php
namespace App\Filament\Resources\PropertyOwners\Pages;
use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyOwners\PropertyOwnerResource;
class CreatePropertyOwner extends AuditedCreateRecord
{
    protected static string $resource = PropertyOwnerResource::class;
}
