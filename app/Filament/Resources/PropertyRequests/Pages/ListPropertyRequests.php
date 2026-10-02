<?php
namespace App\Filament\Resources\PropertyRequests\Pages;
use App\Filament\Resources\PropertyRequests\PropertyRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPropertyRequests extends ListRecords
{
    protected static string $resource = PropertyRequestResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}
