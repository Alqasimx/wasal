<?php

namespace App\Filament\Resources\PropertyFeatures\Pages;

use App\Filament\Resources\PropertyFeatures\PropertyFeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyFeatures extends ListRecords
{
    protected static string $resource = PropertyFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
