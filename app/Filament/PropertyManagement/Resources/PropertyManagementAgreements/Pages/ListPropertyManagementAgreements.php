<?php

namespace App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\Pages;

use App\Filament\PropertyManagement\Resources\PropertyManagementAgreements\PropertyManagementAgreementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyManagementAgreements extends ListRecords
{
    protected static string $resource = PropertyManagementAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة عقار للإدارة'),
        ];
    }
}
