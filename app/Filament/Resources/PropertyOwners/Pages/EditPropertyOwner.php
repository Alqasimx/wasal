<?php
namespace App\Filament\Resources\PropertyOwners\Pages;
use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyOwners\PropertyOwnerResource;
class EditPropertyOwner extends AuditedEditRecord
{
    protected static string $resource = PropertyOwnerResource::class;
}
