<?php

namespace App\Filament\Resources\PropertyFeatures\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\PropertyFeatures\PropertyFeatureResource;

class CreatePropertyFeature extends AuditedCreateRecord
{
    protected static string $resource = PropertyFeatureResource::class;
}
