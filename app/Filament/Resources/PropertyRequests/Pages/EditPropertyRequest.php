<?php
namespace App\Filament\Resources\PropertyRequests\Pages;
use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
class EditPropertyRequest extends AuditedEditRecord
{
    protected static string $resource = PropertyRequestResource::class;
}
