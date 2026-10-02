<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Resources\PropertyListings\PropertyListingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyListings extends ListRecords
{
    protected static string $resource = PropertyListingResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
