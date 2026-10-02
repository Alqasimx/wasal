<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PropertyListing extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SOLD = 'sold';
    public const STATUS_RENTED = 'rented';

    protected $fillable = [
        'property_id', 'listing_number', 'purpose', 'price', 'currency_id',
        'price_period', 'public_title', 'public_description', 'status',
        'published_at', 'expires_at', 'created_by_user_id', 'reviewed_by_user_id',
        'featured_until', 'reviewed_at', 'share_token', 'share_enabled', 'share_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', 'published_at' => 'datetime', 'expires_at' => 'datetime',
            'featured_until' => 'datetime', 'reviewed_at' => 'datetime', 'share_enabled' => 'boolean', 'share_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $listing) => $listing->share_token ??= Str::random(48));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }

    public function versions(): HasMany
    {
        return $this->hasMany(PropertyListingVersion::class);
    }
}
