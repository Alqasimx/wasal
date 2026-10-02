<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Street extends Model
{
    protected $fillable = ['city_id', 'district_id', 'name_ar', 'name_en', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
}
