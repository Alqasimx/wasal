<?php

namespace App\Filament\PropertyManagement\Resources\PropertyExpenses\Pages;

use App\Filament\PropertyManagement\Resources\PropertyExpenses\PropertyExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyExpenses extends ListRecords
{
    protected static string $resource = PropertyExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة مصروف')];
    }
}
