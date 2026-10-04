<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyService extends Model
{
    use SoftDeletes;

    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_CLEANING = 'cleaning';
    public const CATEGORY_INSPECTION = 'inspection';
    public const CATEGORY_UTILITY = 'utility';
    public const CATEGORY_ADMIN = 'admin';
    public const CATEGORY_FINANCIAL = 'financial';

    public const FREQUENCY_ONCE = 'once';
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_QUARTERLY = 'quarterly';
    public const FREQUENCY_SEMIANNUAL = 'semiannual';
    public const FREQUENCY_ANNUAL = 'annual';
    public const FREQUENCY_CUSTOM_DAYS = 'custom_days';

    protected $fillable = [
        'code',
        'name_ar',
        'name_en',
        'category',
        'description',
        'default_frequency',
        'default_interval',
        'default_cost',
        'currency_id',
        'notify_before_minutes',
        'creates_task',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_interval' => 'integer',
            'default_cost' => 'decimal:2',
            'notify_before_minutes' => 'integer',
            'creates_task' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PropertyServiceSchedule::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
