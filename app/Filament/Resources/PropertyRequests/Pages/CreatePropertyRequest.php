<?php
namespace App\Filament\Resources\PropertyRequests\Pages;
use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
class CreatePropertyRequest extends AuditedCreateRecord
{
    protected static string $resource = PropertyRequestResource::class;
}
