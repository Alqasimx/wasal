<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Governorate;
use App\Models\Property;
use App\Models\PropertyListing;
use App\Models\PropertyListingVersion;
use App\Models\PropertyType;
use App\Models\Role;
use App\Models\User;
use App\Services\PropertyListingWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyListingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_listing_changes_are_saved_as_a_draft_without_changing_live_listing(): void
    {
        [$listing, $actor] = $this->publishedListing();

        $version = app(PropertyListingWorkflowService::class)->draftPublishedChanges(
            listing: $listing,
            data: [
                'public_title' => 'عنوان جديد ينتظر المراجعة',
                'price' => 75000,
            ],
            actor: $actor,
        );

        $listing->refresh();

        $this->assertSame('شقة منشورة', $listing->public_title);
        $this->assertSame('50000.00', $listing->price);
        $this->assertSame(PropertyListing::STATUS_PUBLISHED, $listing->status);

        $this->assertSame(PropertyListingVersion::STATUS_DRAFT, $version->status);
        $this->assertSame('عنوان جديد ينتظر المراجعة', $version->payload['public_title']);
        $this->assertSame(75000, $version->payload['price']);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => PropertyListing::class,
            'entity_id' => $listing->id,
            'action' => 'property_listing.version_drafted',
        ]);
    }

    public function test_published_revision_can_be_submitted_without_hiding_live_listing(): void
    {
        [$listing, $actor] = $this->publishedListing();
        $workflow = app(PropertyListingWorkflowService::class);

        $workflow->draftPublishedChanges(
            listing: $listing,
            data: [
                'public_title' => 'نسخة جديدة',
                'price' => 60000,
            ],
            actor: $actor,
        );

        $version = $workflow->submitForReview(
            listing: $listing,
            actor: $actor,
        );

        $this->assertSame(PropertyListingVersion::STATUS_PENDING, $version->status);
        $this->assertSame(PropertyListing::STATUS_PUBLISHED, $listing->fresh()->status);
        $this->assertSame('شقة منشورة', $listing->fresh()->public_title);
    }

    public function test_approved_revision_replaces_live_listing_and_writes_audit_log(): void
    {
        [$listing, $actor] = $this->publishedListing();
        $workflow = app(PropertyListingWorkflowService::class);

        $workflow->draftPublishedChanges(
            listing: $listing,
            data: [
                'public_title' => 'شقة بعد الاعتماد',
                'price' => 82000,
            ],
            actor: $actor,
        );

        $workflow->submitForReview(
            listing: $listing,
            actor: $actor,
        );

        $workflow->approveAndPublish(
            listing: $listing,
            actor: $actor,
            reviewNotes: 'تمت المراجعة',
        );

        $listing->refresh();

        $this->assertSame('شقة بعد الاعتماد', $listing->public_title);
        $this->assertSame('82000.00', $listing->price);
        $this->assertSame(PropertyListing::STATUS_PUBLISHED, $listing->status);
        $this->assertSame($actor->id, $listing->reviewed_by_user_id);

        $this->assertDatabaseHas('property_listing_versions', [
            'property_listing_id' => $listing->id,
            'status' => PropertyListingVersion::STATUS_APPROVED,
            'reviewed_by_user_id' => $actor->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => PropertyListing::class,
            'entity_id' => $listing->id,
            'action' => 'property_listing.approved_and_published',
        ]);
    }

    public function test_requesting_changes_for_a_published_revision_keeps_live_listing_published(): void
    {
        [$listing, $actor] = $this->publishedListing();
        $workflow = app(PropertyListingWorkflowService::class);

        $workflow->draftPublishedChanges(
            listing: $listing,
            data: [
                'public_title' => 'عنوان يحتاج تعديل',
            ],
            actor: $actor,
        );

        $workflow->submitForReview(
            listing: $listing,
            actor: $actor,
        );

        $version = $workflow->requestChanges(
            listing: $listing,
            actor: $actor,
            reviewNotes: 'يرجى تحسين العنوان',
        );

        $this->assertSame(PropertyListingVersion::STATUS_CHANGES_REQUESTED, $version->status);
        $this->assertSame(PropertyListing::STATUS_PUBLISHED, $listing->fresh()->status);
        $this->assertSame('شقة منشورة', $listing->fresh()->public_title);
    }

    public function test_initial_draft_submission_enters_pending_review_state(): void
    {
        [$listing, $actor] = $this->draftListing();

        $version = app(PropertyListingWorkflowService::class)->submitForReview(
            listing: $listing,
            actor: $actor,
        );

        $this->assertSame(PropertyListingVersion::STATUS_PENDING, $version->status);
        $this->assertSame(PropertyListing::STATUS_PENDING_REVIEW, $listing->fresh()->status);
    }

    public function test_property_reviewer_receives_separate_review_and_publish_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $reviewer = Role::findByName('property_reviewer', 'web');

        $this->assertTrue($reviewer->hasPermissionTo('property_listings.manage'));
        $this->assertTrue($reviewer->hasPermissionTo('property_listings.review'));
        $this->assertTrue($reviewer->hasPermissionTo('property_listings.publish'));
    }

    private function publishedListing(): array
    {
        return $this->makeListing(PropertyListing::STATUS_PUBLISHED, now());
    }

    private function draftListing(): array
    {
        return $this->makeListing(PropertyListing::STATUS_DRAFT);
    }

    private function makeListing(string $status, $publishedAt = null): array
    {
        $actor = User::factory()->create();

        $country = Country::create([
            'code' => 'YE',
            'name_ar' => 'اليمن',
            'name_en' => 'Yemen',
            'is_active' => true,
        ]);

        $governorate = Governorate::create([
            'country_id' => $country->id,
            'code' => 'AD',
            'name_ar' => 'عدن',
            'name_en' => 'Aden',
            'is_active' => true,
        ]);

        $city = City::create([
            'governorate_id' => $governorate->id,
            'code' => 'ADEN',
            'name_ar' => 'عدن',
            'name_en' => 'Aden',
            'is_active' => true,
        ]);

        $currency = Currency::create([
            'code' => 'YER',
            'iso_code' => 'YER',
            'name_ar' => 'ريال يمني',
            'name_en' => 'Yemeni Rial',
            'is_active' => true,
        ]);

        $type = PropertyType::create([
            'name_ar' => 'شقة',
            'name_en' => 'Apartment',
            'slug' => 'apartment',
            'is_active' => true,
        ]);

        $property = Property::create([
            'created_by_user_id' => $actor->id,
            'property_type_id' => $type->id,
            'internal_code' => 'WF-'.uniqid(),
            'title_ar' => 'عقار اختبار',
            'status' => 'active',
            'city_id' => $city->id,
        ]);

        $listing = PropertyListing::create([
            'property_id' => $property->id,
            'listing_number' => 'LIST-'.uniqid(),
            'purpose' => 'rent',
            'price' => 50000,
            'currency_id' => $currency->id,
            'price_period' => 'monthly',
            'public_title' => $status === PropertyListing::STATUS_PUBLISHED
                ? 'شقة منشورة'
                : 'شقة مسودة',
            'public_description' => 'وصف اختبار',
            'status' => $status,
            'published_at' => $publishedAt,
            'created_by_user_id' => $actor->id,
            'share_enabled' => true,
        ]);

        return [$listing, $actor];
    }
}
