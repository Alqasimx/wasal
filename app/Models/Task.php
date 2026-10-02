<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Task extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const RECURRENCE_ONCE = 'once';
    public const RECURRENCE_DAILY = 'daily';
    public const RECURRENCE_WEEKLY = 'weekly';

    protected $fillable = [
        'title',
        'description',
        'related_type',
        'related_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'due_at',
        'recurrence',
        'status',
        'notify_before_minutes',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'notified_at' => 'datetime',
            'notify_before_minutes' => 'integer',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeAwaitingNotification(Builder $query, Carbon $now): Builder
    {
        $query = $query
            ->whereNotNull('assigned_to_user_id')
            ->whereNotNull('due_at')
            ->whereNull('notified_at')
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);

        if (DB::connection()->getDriverName() === 'sqlite') {
            return $query->whereRaw(
                "datetime(due_at) <= datetime(?, '+' || notify_before_minutes || ' minutes')",
                [$now->toDateTimeString()],
            );
        }

        return $query->whereRaw(
            'due_at <= DATE_ADD(?, INTERVAL notify_before_minutes MINUTE)',
            [$now],
        );
    }
}
