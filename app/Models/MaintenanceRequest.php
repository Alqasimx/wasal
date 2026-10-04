<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MaintenanceRequest extends Model
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_OPEN = 'open';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const COST_OWNER = 'owner';
    public const COST_TENANT = 'tenant';
    public const COST_WASAL = 'wasal';
    public const COST_SHARED = 'shared';

    protected $fillable = [
        'reference_number',
        'property_id',
        'property_unit_id',
        'tenant_id',
        'property_service_id',
        'property_service_schedule_id',
        'property_vendor_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'description',
        'priority',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'currency_id',
        'cost_bearer',
        'is_paid',
        'attachments',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'is_paid' => 'boolean',
            'attachments' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            $request->reference_number ??= 'MNT-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(PropertyService::class, 'property_service_id');
    }

    public function serviceSchedule(): BelongsTo
    {
        return $this->belongsTo(PropertyServiceSchedule::class, 'property_service_schedule_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(PropertyVendor::class, 'property_vendor_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
