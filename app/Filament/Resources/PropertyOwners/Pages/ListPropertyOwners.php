<?php
namespace App\Filament\Resources\PropertyOwners\Pages;
use App\Filament\Resources\PropertyOwners\PropertyOwnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPropertyOwners extends ListRecords
{
    protected static string $resource = PropertyOwnerResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
