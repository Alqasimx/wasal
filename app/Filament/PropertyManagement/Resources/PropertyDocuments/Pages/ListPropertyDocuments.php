<?php

namespace App\Filament\PropertyManagement\Resources\PropertyDocuments\Pages;

use App\Filament\PropertyManagement\Resources\PropertyDocuments\PropertyDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyDocuments extends ListRecords
{
    protected static string $resource = PropertyDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة مستند')];
    }
}
