<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\Resources\Settings\SettingResource;

class CreateSetting extends AuditedCreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();
        $data['updated_at'] = now();

        return $data;
    }
}
