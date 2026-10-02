<?php

namespace App\Filament\Resources\PropertyFeatures\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyFeatures\PropertyFeatureResource;

class EditPropertyFeature extends AuditedEditRecord
{
    protected static string $resource = PropertyFeatureResource::class;
}
