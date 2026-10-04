<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyServiceSchedule extends Model
{
    protected $fillable = [
        'property_service_id',
        'property_id',
        'property_unit_id',
        'property_vendor_id',
        'assigned_to_user_id',
        'frequency',
        'interval_count',
        'starts_at',
        'next_due_at',
        'last_generated_due_at',
        'last_completed_at',
        'notify_before_minutes',
        'estimated_cost',
        'currency_id',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'interval_count' => 'integer',
            'starts_at' => 'date',
            'next_due_at' => 'datetime',
            'last_generated_due_at' => 'datetime',
            'last_completed_at' => 'datetime',
            'notify_before_minutes' => 'integer',
            'estimated_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(PropertyService::class, 'property_service_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(PropertyVendor::class, 'property_vendor_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
