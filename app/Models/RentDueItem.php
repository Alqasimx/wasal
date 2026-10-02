<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentDueItem extends Model
{
    public const STATUS_DUE = 'due';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenancy_id',
        'due_date',
        'amount',
        'currency_id',
        'status',
        'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function tenancy(): BelongsTo
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function isDue(): bool
    {
        return in_array($this->status, [
            self::STATUS_DUE,
            self::STATUS_PARTIAL,
            self::STATUS_OVERDUE,
        ], true)
            && $this->paid_amount < $this->amount;
    }
}
