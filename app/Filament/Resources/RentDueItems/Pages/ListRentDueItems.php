<?php

namespace App\Filament\Resources\RentDueItems\Pages;

use App\Filament\Resources\RentDueItems\RentDueItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentDueItems extends ListRecords
{
    protected static string $resource = RentDueItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة استحقاق'),
        ];
    }
}
