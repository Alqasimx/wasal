<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyListingResource;
use App\Models\PropertyListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $listings = PropertyListing::query()
            ->published()
            ->when($request->filled('purpose'), fn ($query) => $query->where('purpose', $request->string('purpose')))
            ->when($request->filled('property_type_id'), fn ($query) => $query->whereHas('property', fn ($property) => $property->where('property_type_id', $request->integer('property_type_id'))))
            ->when($request->filled('city_id'), fn ($query) => $query->whereHas('property', fn ($property) => $property->where('city_id', $request->integer('city_id'))))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->input('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->input('max_price')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q').'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('public_title', 'like', $search)
                        ->orWhere('public_description', 'like', $search)
                        ->orWhereHas('property', fn ($property) => $property->where('title_ar', 'like', $search));
                });
            })
            ->when(is_array($request->input('attribute')), function ($query) use ($request): void {
                foreach ($request->input('attribute', []) as $key => $value) {
                    $query->whereHas('property.attributeValues', function ($values) use ($key, $value): void {
                        $values->whereHas('feature', fn ($feature) => $feature->where('key', $key)->where('is_filterable', true))
                            ->where(function ($values) use ($value): void {
                                if (is_numeric($value)) {
                                    $values->where('value_number', $value);
                                } elseif (in_array(strtolower((string) $value), ['true', 'false'], true)) {
                                    $values->where('value_boolean', strtolower((string) $value) === 'true');
                                } else {
                                    $values->where('value_text', $value);
                                }
                            });
                    });
                }
            })
            ->with(['property.propertyType', 'property.city', 'property.attributeValues.feature', 'currency'])
            ->latest('published_at')
            ->paginate(20);

        return PropertyListingResource::collection($listings);
    }

    public function show(PropertyListing $listing): PropertyListingResource
    {
        abort_unless($listing->isPublished(), 404);

        return new PropertyListingResource(
            $listing->load(['property.propertyType', 'property.city', 'property.attributeValues.feature', 'currency'])
        );
    }

    public function shared(string $token): PropertyListingResource
    {
        $listing = PropertyListing::query()
            ->published()
            ->where('share_enabled', true)
            ->where('share_token', $token)
            ->firstOrFail();

        $listing->increment('share_count');

        return new PropertyListingResource(
            $listing->load(['property.propertyType', 'property.city', 'property.attributeValues.feature', 'currency'])
        );
    }
}
