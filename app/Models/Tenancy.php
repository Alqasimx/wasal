<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenancy extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';

    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_QUARTERLY = 'quarterly';
    public const FREQUENCY_SEMIANNUAL = 'semiannual';
    public const FREQUENCY_ANNUAL = 'annual';

    protected $fillable = [
        'contract_number',
        'property_unit_id',
        'tenant_id',
        'starts_at',
        'ends_at',
        'rent_amount',
        'currency_id',
        'payment_frequency',
        'due_day',
        'grace_days',
        'security_deposit',
        'auto_generate_dues',
        'contract_attachment_path',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'rent_amount' => 'decimal:2',
            'due_day' => 'integer',
            'grace_days' => 'integer',
            'security_deposit' => 'decimal:2',
            'auto_generate_dues' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $tenancy): void {
            $tenancy->contract_number ??=
                'TEN-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function dueItems(): HasMany
    {
        return $this->hasMany(RentDueItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    public function outstandingBalance(): float
    {
        return (float) $this->dueItems()
            ->whereNotIn('status', [RentDueItem::STATUS_PAID, RentDueItem::STATUS_CANCELLED])
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as balance')
            ->value('balance');
    }
}
