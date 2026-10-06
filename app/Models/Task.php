<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Task extends Model
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

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
        'priority',
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

    protected static function booted(): void
    {
        static::updated(function (self $task): void {
            if (! $task->wasChanged('status')
                || $task->status !== self::STATUS_COMPLETED
                || $task->recurrence === self::RECURRENCE_ONCE
                || ! $task->due_at) {
                return;
            }

            $nextDueAt = $task->recurrence === self::RECURRENCE_WEEKLY
                ? $task->due_at->copy()->addWeek()
                : $task->due_at->copy()->addDay();

            $alreadyCreated = self::query()
                ->where('related_type', $task->related_type)
                ->where('related_id', $task->related_id)
                ->where('title', $task->title)
                ->where('due_at', $nextDueAt)
                ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS])
                ->exists();

            if ($alreadyCreated) {
                return;
            }

            self::create([
                'title' => $task->title,
                'description' => $task->description,
                'related_type' => $task->related_type,
                'related_id' => $task->related_id,
                'assigned_to_user_id' => $task->assigned_to_user_id,
                'created_by_user_id' => $task->created_by_user_id,
                'due_at' => $nextDueAt,
                'recurrence' => $task->recurrence,
                'status' => self::STATUS_PENDING,
                'priority' => $task->priority ?: self::PRIORITY_NORMAL,
                'notify_before_minutes' => $task->notify_before_minutes,
                'notified_at' => null,
            ]);
        });
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
                "datetime(due_at) <= datetime(?, '+' || COALESCE(notify_before_minutes, 0) || ' minutes')",
                [$now->toDateTimeString()],
            );
        }

        return $query->whereRaw(
            'due_at <= DATE_ADD(?, INTERVAL COALESCE(notify_before_minutes, 0) MINUTE)',
            [$now],
        );
    }
}
