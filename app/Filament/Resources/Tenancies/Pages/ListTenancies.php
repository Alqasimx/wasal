<?php

namespace App\Filament\Resources\Tenancies\Pages;

use App\Filament\Resources\Tenancies\TenancyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTenancies extends ListRecords
{
    protected static string $resource = TenancyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة عقد'),
        ];
    }
}
