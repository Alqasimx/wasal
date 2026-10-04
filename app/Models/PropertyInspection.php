<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PropertyInspection extends Model
{
    public const TYPE_PERIODIC = 'periodic';
    public const TYPE_CHECK_IN = 'check_in';
    public const TYPE_CHECK_OUT = 'check_out';
    public const TYPE_CONDITION = 'condition';

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'inspection_number','property_id','property_unit_id','tenancy_id',
        'inspection_type','status','scheduled_at','started_at','completed_at',
        'inspector_user_id','condition_score','findings','attachments','notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'condition_score' => 'integer',
            'findings' => 'array',
            'attachments' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $inspection): void {
            $inspection->inspection_number ??=
                'INS-'.now()->format('Ym').'-'.Str::upper(Str::random(8));
        });
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function unit(): BelongsTo { return $this->belongsTo(PropertyUnit::class, 'property_unit_id'); }
    public function tenancy(): BelongsTo { return $this->belongsTo(Tenancy::class); }
    public function inspector(): BelongsTo { return $this->belongsTo(User::class, 'inspector_user_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
