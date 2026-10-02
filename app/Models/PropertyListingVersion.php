<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyListingVersion extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['property_listing_id', 'version_number', 'payload', 'status', 'submitted_by_user_id', 'reviewed_by_user_id', 'review_notes', 'reviewed_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'reviewed_at' => 'datetime'];
    }

    public function listing(): BelongsTo { return $this->belongsTo(PropertyListing::class, 'property_listing_id'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by_user_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}
