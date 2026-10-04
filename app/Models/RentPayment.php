<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RentPayment extends Model
{
    public const STATUS_POSTED = 'posted';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'receipt_number',
        'rent_due_item_id',
        'tenancy_id',
        'amount',
        'currency_id',
        'paid_at',
        'payment_method',
        'bank_id',
        'reference_number',
        'attachment_path',
        'notes',
        'status',
        'recorded_by_user_id',
        'voided_at',
        'voided_by_user_id',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            $payment->receipt_number ??= 'RCP-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function dueItem(): BelongsTo
    {
        return $this->belongsTo(RentDueItem::class, 'rent_due_item_id');
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }
}
