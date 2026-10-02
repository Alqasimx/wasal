<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PropertyListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_number' => $this->listing_number,
            'purpose' => $this->purpose,
            'price' => $this->price,
            'price_period' => $this->price_period,
            'public_title' => $this->public_title,
            'public_description' => $this->public_description,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'share_url' => $this->share_token
                ? url('/api/v1/shared-offers/'.$this->share_token)
                : null,
            'property' => [
                'id' => $this->property?->id,
                'title_ar' => $this->property?->title_ar,
                'property_type' => $this->property?->propertyType?->only(['id', 'name_ar', 'name_en', 'slug']),
                'city' => $this->property?->city?->only(['id', 'name_ar', 'name_en']),
                'public_location_text' => $this->property?->public_location_text,
                'public_latitude' => $this->property?->public_latitude,
                'public_longitude' => $this->property?->public_longitude,
                'gallery' => collect($this->property?->gallery ?? [])
                    ->map(fn (string $path): string => Storage::disk('public')->url($path))
                    ->values()
                    ->all(),
                'attributes' => $this->property?->relationLoaded('attributeValues')
                    ? PropertyAttributeValueResource::collection($this->property->attributeValues)
                    : [],
            ],
        ];
    }
}
