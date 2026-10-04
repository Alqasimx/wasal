<?php

namespace App\Filament\PropertyManagement\Resources\UtilityMeterReadings\Pages;

use App\Filament\Pages\AuditedCreateRecord;
use App\Filament\PropertyManagement\Resources\UtilityMeterReadings\UtilityMeterReadingResource;
use App\Models\UtilityMeterReading;
use Illuminate\Validation\ValidationException;

class CreateUtilityMeterReading extends AuditedCreateRecord
{
    protected static string $resource = UtilityMeterReadingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $previous = UtilityMeterReading::query()
            ->where('utility_meter_id', $data['utility_meter_id'])
            ->where('reading_at', '<', $data['reading_at'])
            ->latest('reading_at')
            ->value('reading_value');

        $current = (float) $data['reading_value'];

        if ($previous !== null && $current < (float) $previous) {
            throw ValidationException::withMessages([
                'reading_value' => 'القراءة الحالية لا يمكن أن تكون أقل من القراءة السابقة.',
            ]);
        }

        $data['previous_value'] = $previous;
        $data['consumption'] = $previous !== null
            ? $current - (float) $previous
            : null;
        $data['recorded_by_user_id'] = auth()->id();

        return $data;
    }
}
