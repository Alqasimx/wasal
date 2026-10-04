<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'property_unit_id',
        'tenant_id',
        'starts_at',
        'ends_at',
        'rent_amount',
        'currency_id',
        'payment_frequency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'rent_amount' => 'decimal:2',
        ];
    }

    public function unit(): BelongsTo { return $this->belongsTo(PropertyUnit::class, 'property_unit_id'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function dueItems(): HasMany { return $this->hasMany(RentDueItem::class); }
    public function payments(): HasMany { return $this->hasMany(RentPayment::class); }
}
