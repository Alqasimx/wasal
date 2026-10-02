<?php
namespace App\Filament\Resources\PropertyUnits\Pages;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPropertyUnits extends ListRecords
{
    protected static string $resource = PropertyUnitResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
