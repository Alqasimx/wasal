<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PropertyManagementAgreement extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ENDED = 'ended';

    public const FEE_PERCENTAGE = 'percentage';
    public const FEE_FIXED = 'fixed';

    public const BILLING_MONTHLY = 'monthly';
    public const BILLING_QUARTERLY = 'quarterly';
    public const BILLING_SEMIANNUAL = 'semiannual';
    public const BILLING_ANNUAL = 'annual';

    protected $fillable = [
        'agreement_number',
        'property_id',
        'property_owner_id',
        'assigned_manager_user_id',
        'created_by_user_id',
        'starts_at',
        'ends_at',
        'status',
        'management_fee_type',
        'management_fee_value',
        'fee_billing_frequency',
        'auto_renew',
        'renewal_notice_days',
        'currency_id',
        'included_services',
        'agreement_document_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'management_fee_value' => 'decimal:2',
            'auto_renew' => 'boolean',
            'renewal_notice_days' => 'integer',
            'included_services' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $agreement): void {
            $agreement->agreement_number ??=
                'PMA-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function propertyOwner(): BelongsTo
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(PropertyExpense::class);
    }

    public function ownerSettlements(): HasMany
    {
        return $this->hasMany(OwnerSettlement::class);
    }

    public function activeUnitsCount(): int
    {
        return $this->property
            ? $this->property->units()
                ->whereHas('tenancies', fn ($query) => $query->where('status', Tenancy::STATUS_ACTIVE))
                ->count()
            : 0;
    }

    public function unitsCount(): int
    {
        return $this->property?->units()->count() ?? 0;
    }

    public function occupancyPercentage(): float
    {
        $units = $this->unitsCount();

        return $units > 0
            ? round(($this->activeUnitsCount() / $units) * 100, 1)
            : 0.0;
    }
}
