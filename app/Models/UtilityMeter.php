<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UtilityMeter extends Model
{
    public const TYPE_ELECTRICITY = 'electricity';
    public const TYPE_WATER = 'water';
    public const TYPE_GAS = 'gas';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'property_id','property_unit_id','meter_type','meter_number',
        'unit_of_measure','is_active','notes',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function unit(): BelongsTo { return $this->belongsTo(PropertyUnit::class, 'property_unit_id'); }
    public function readings(): HasMany { return $this->hasMany(UtilityMeterReading::class); }
}
