<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyListingResource;
use App\Models\PropertyListing;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyListingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $listings = PropertyListing::query()
            ->published()
            ->with(['property.propertyType', 'property.city', 'currency'])
            ->latest('published_at')
            ->paginate(20);

        return PropertyListingResource::collection($listings);
    }

    public function show(PropertyListing $listing): PropertyListingResource
    {
        abort_unless($listing->isPublished(), 404);

        return new PropertyListingResource(
            $listing->load(['property.propertyType', 'property.city', 'currency'])
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
            $listing->load(['property.propertyType', 'property.city', 'currency'])
        );
    }
}
