<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PropertyRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_SEARCHING = 'searching';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    protected $fillable = ['reference_number','user_id','source','purpose','property_type_id','city_id','district_id','neighborhood_id','min_price','max_price','currency_id','requirements_json','wants_field_search','assigned_to_user_id','status','notes'];
    protected function casts(): array { return ['min_price'=>'decimal:2','max_price'=>'decimal:2','requirements_json'=>'array','wants_field_search'=>'boolean']; }
    protected static function booted(): void
    {
        static::creating(fn (self $request) => $request->reference_number ??= 'REQ-'.str()->upper(str()->random(10)));
        static::created(fn (self $request) => $request->statusHistory()->create(['to_status' => $request->status, 'changed_by_user_id' => auth()->id()]));
        static::updated(function (self $request): void {
            if ($request->wasChanged('status')) {
                $request->statusHistory()->create([
                    'from_status' => $request->getOriginal('status'),
                    'to_status' => $request->status,
                    'changed_by_user_id' => auth()->id(),
                ]);
            }
        });
    }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_user_id'); }
    public function propertyType(): BelongsTo { return $this->belongsTo(PropertyType::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function district(): BelongsTo { return $this->belongsTo(District::class); }
    public function neighborhood(): BelongsTo { return $this->belongsTo(Neighborhood::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function statusHistory(): HasMany { return $this->hasMany(PropertyRequestStatusHistory::class); }
}
