<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\Settings\SettingResource;

class EditSetting extends AuditedEditRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by_user_id'] = auth()->id();
        $data['updated_at'] = now();

        return $data;
    }
}
