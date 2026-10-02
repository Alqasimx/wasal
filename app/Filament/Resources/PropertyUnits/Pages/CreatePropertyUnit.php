<?php
namespace App\Filament\Resources\PropertyUnits\Pages;
use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
class CreatePropertyUnit extends AuditedCreateRecord
{
    protected static string $resource = PropertyUnitResource::class;
}
