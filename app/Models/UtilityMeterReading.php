<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityMeterReading extends Model
{
    protected $fillable = [
        'utility_meter_id','reading_at','reading_value','previous_value',
        'consumption','recorded_by_user_id','attachment_path','notes',
    ];

    protected function casts(): array
    {
        return [
            'reading_at' => 'datetime',
            'reading_value' => 'decimal:3',
            'previous_value' => 'decimal:3',
            'consumption' => 'decimal:3',
        ];
    }

    public function meter(): BelongsTo { return $this->belongsTo(UtilityMeter::class, 'utility_meter_id'); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_user_id'); }
}
