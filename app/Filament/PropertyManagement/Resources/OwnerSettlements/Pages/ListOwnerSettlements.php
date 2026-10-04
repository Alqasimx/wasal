<?php

namespace App\Filament\PropertyManagement\Resources\OwnerSettlements\Pages;

use App\Filament\PropertyManagement\Resources\OwnerSettlements\OwnerSettlementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOwnerSettlements extends ListRecords
{
    protected static string $resource = OwnerSettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إنشاء تسوية'),
        ];
    }
}
