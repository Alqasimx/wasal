<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OwnerSettlement extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'settlement_number',
        'property_management_agreement_id',
        'property_owner_id',
        'property_id',
        'period_start',
        'period_end',
        'currency_id',
        'gross_collections',
        'owner_expenses',
        'management_fee',
        'net_payable',
        'status',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'paid_by_user_id',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'gross_collections' => 'decimal:2',
            'owner_expenses' => 'decimal:2',
            'management_fee' => 'decimal:2',
            'net_payable' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $settlement): void {
            $settlement->settlement_number ??=
                'SET-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(PropertyManagementAgreement::class, 'property_management_agreement_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(PropertyOwner::class, 'property_owner_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}
