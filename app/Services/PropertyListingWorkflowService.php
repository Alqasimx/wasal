<?php

namespace App\Services;

use App\Models\PropertyListing;
use App\Models\PropertyListingVersion;
use App\Models\User;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PropertyListingWorkflowService
{
    private const EDITABLE_FIELDS = [
        'property_id',
        'listing_number',
        'purpose',
        'price',
        'currency_id',
        'price_period',
        'public_title',
        'public_description',
        'expires_at',
        'share_enabled',
    ];

    public function __construct(
        private readonly AuditService $auditService,
    ) {
    }

    public function editableFields(): array
    {
        return self::EDITABLE_FIELDS;
    }

    public function draftPublishedChanges(
        PropertyListing $listing,
        array $data,
        User $actor,
        ?Request $request = null,
    ): PropertyListingVersion {
        if ($listing->status !== PropertyListing::STATUS_PUBLISHED) {
            throw new DomainException('Only published listings use revision drafts.');
        }

        return DB::transaction(function () use ($listing, $data, $actor, $request): PropertyListingVersion {
            $lockedListing = $this->lockListing($listing);

            if ($this->pendingVersion($lockedListing)) {
                throw new DomainException('This listing already has a revision pending review.');
            }

            $payload = Arr::only($data, self::EDITABLE_FIELDS);

            $version = $lockedListing->versions()
                ->whereIn('status', [
                    PropertyListingVersion::STATUS_DRAFT,
                    PropertyListingVersion::STATUS_CHANGES_REQUESTED,
                ])
                ->latest('version_number')
                ->first();

            $oldPayload = $version?->payload;

            if ($version) {
                $version->update([
                    'payload' => $payload,
                    'status' => PropertyListingVersion::STATUS_DRAFT,
                    'submitted_by_user_id' => $actor->id,
                    'reviewed_by_user_id' => null,
                    'review_notes' => null,
                    'reviewed_at' => null,
                ]);
            } else {
                $version = $lockedListing->versions()->create([
                    'version_number' => $this->nextVersionNumber($lockedListing),
                    'payload' => $payload,
                    'status' => PropertyListingVersion::STATUS_DRAFT,
                    'submitted_by_user_id' => $actor->id,
                ]);
            }

            $this->auditService->forModel(
                action: 'property_listing.version_drafted',
                model: $lockedListing,
                oldValues: $oldPayload,
                newValues: [
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'payload' => $version->payload,
                ],
                actor: $actor,
                request: $request,
            );

            return $version->fresh();
        });
    }

    public function submitForReview(
        PropertyListing $listing,
        User $actor,
        ?Request $request = null,
    ): PropertyListingVersion {
        return DB::transaction(function () use ($listing, $actor, $request): PropertyListingVersion {
            $lockedListing = $this->lockListing($listing);

            if ($this->pendingVersion($lockedListing)) {
                throw new DomainException('This listing already has a version pending review.');
            }

            $wasPublished = $lockedListing->status === PropertyListing::STATUS_PUBLISHED;

            if ($wasPublished) {
                $version = $lockedListing->versions()
                    ->whereIn('status', [
                        PropertyListingVersion::STATUS_DRAFT,
                        PropertyListingVersion::STATUS_CHANGES_REQUESTED,
                    ])
                    ->latest('version_number')
                    ->first();

                if (! $version) {
                    throw new DomainException('Create a revision draft before submitting this published listing.');
                }

                $version->update([
                    'status' => PropertyListingVersion::STATUS_PENDING,
                    'submitted_by_user_id' => $actor->id,
                    'reviewed_by_user_id' => null,
                    'review_notes' => null,
                    'reviewed_at' => null,
                ]);
            } else {
                if (! in_array($lockedListing->status, [
                    PropertyListing::STATUS_DRAFT,
                    PropertyListing::STATUS_CHANGES_REQUESTED,
                ], true)) {
                    throw new DomainException('This listing cannot be submitted from its current state.');
                }

                $version = $lockedListing->versions()
                    ->whereIn('status', [
                        PropertyListingVersion::STATUS_DRAFT,
                        PropertyListingVersion::STATUS_CHANGES_REQUESTED,
                    ])
                    ->latest('version_number')
                    ->first();

                if ($version) {
                    $version->update([
                        'payload' => $this->listingPayload($lockedListing),
                        'status' => PropertyListingVersion::STATUS_PENDING,
                        'submitted_by_user_id' => $actor->id,
                        'reviewed_by_user_id' => null,
                        'review_notes' => null,
                        'reviewed_at' => null,
                    ]);
                } else {
                    $version = $lockedListing->versions()->create([
                        'version_number' => $this->nextVersionNumber($lockedListing),
                        'payload' => $this->listingPayload($lockedListing),
                        'status' => PropertyListingVersion::STATUS_PENDING,
                        'submitted_by_user_id' => $actor->id,
                    ]);
                }

                $lockedListing->update([
                    'status' => PropertyListing::STATUS_PENDING_REVIEW,
                ]);
            }

            $this->auditService->forModel(
                action: 'property_listing.submitted_for_review',
                model: $lockedListing,
                oldValues: [
                    'listing_status' => $listing->status,
                ],
                newValues: [
                    'listing_status' => $lockedListing->fresh()->status,
                    'version_id' => $version->id,
                    'version_number' => $version->version_number,
                    'version_status' => PropertyListingVersion::STATUS_PENDING,
                ],
                actor: $actor,
                request: $request,
            );

            return $version->fresh();
        });
    }

    public function approveAndPublish(
        PropertyListing $listing,
        User $actor,
        ?string $reviewNotes = null,
        ?Request $request = null,
    ): PropertyListing {
        return DB::transaction(function () use ($listing, $actor, $reviewNotes, $request): PropertyListing {
            $lockedListing = $this->lockListing($listing);
            $version = $this->pendingVersion($lockedListing);

            if (! $version) {
                throw new DomainException('No pending listing version was found.');
            }

            $oldValues = $this->auditListingValues($lockedListing);

            $lockedListing->fill(
                Arr::only($version->payload ?? [], self::EDITABLE_FIELDS)
            );

            $lockedListing->status = PropertyListing::STATUS_PUBLISHED;
            $lockedListing->published_at ??= now();
            $lockedListing->reviewed_by_user_id = $actor->id;
            $lockedListing->reviewed_at = now();
            $lockedListing->save();

            $version->update([
                'status' => PropertyListingVersion::STATUS_APPROVED,
                'reviewed_by_user_id' => $actor->id,
                'review_notes' => $reviewNotes,
                'reviewed_at' => now(),
            ]);

            $lockedListing->refresh();

            $this->auditService->forModel(
                action: 'property_listing.approved_and_published',
                model: $lockedListing,
                oldValues: $oldValues,
                newValues: $this->auditListingValues($lockedListing) + [
                    'approved_version_id' => $version->id,
                    'approved_version_number' => $version->version_number,
                ],
                actor: $actor,
                request: $request,
            );

            return $lockedListing;
        });
    }

    public function requestChanges(
        PropertyListing $listing,
        User $actor,
        string $reviewNotes,
        ?Request $request = null,
    ): PropertyListingVersion {
        return DB::transaction(function () use ($listing, $actor, $reviewNotes, $request): PropertyListingVersion {
            $lockedListing = $this->lockListing($listing);
            $version = $this->pendingVersion($lockedListing);

            if (! $version) {
                throw new DomainException('No pending listing version was found.');
            }

            $oldListingStatus = $lockedListing->status;

            $version->update([
                'status' => PropertyListingVersion::STATUS_CHANGES_REQUESTED,
                'reviewed_by_user_id' => $actor->id,
                'review_notes' => $reviewNotes,
                'reviewed_at' => now(),
            ]);

            if ($lockedListing->status !== PropertyListing::STATUS_PUBLISHED) {
                $lockedListing->update([
                    'status' => PropertyListing::STATUS_CHANGES_REQUESTED,
                    'reviewed_by_user_id' => $actor->id,
                    'reviewed_at' => now(),
                ]);
            }

            $this->auditService->forModel(
                action: 'property_listing.changes_requested',
                model: $lockedListing,
                oldValues: [
                    'listing_status' => $oldListingStatus,
                    'version_status' => PropertyListingVersion::STATUS_PENDING,
                ],
                newValues: [
                    'listing_status' => $lockedListing->fresh()->status,
                    'version_id' => $version->id,
                    'version_status' => PropertyListingVersion::STATUS_CHANGES_REQUESTED,
                    'review_notes' => $reviewNotes,
                ],
                actor: $actor,
                request: $request,
            );

            return $version->fresh();
        });
    }

    public function reject(
        PropertyListing $listing,
        User $actor,
        string $reviewNotes,
        ?Request $request = null,
    ): PropertyListingVersion {
        return DB::transaction(function () use ($listing, $actor, $reviewNotes, $request): PropertyListingVersion {
            $lockedListing = $this->lockListing($listing);
            $version = $this->pendingVersion($lockedListing);

            if (! $version) {
                throw new DomainException('No pending listing version was found.');
            }

            $oldListingStatus = $lockedListing->status;

            $version->update([
                'status' => PropertyListingVersion::STATUS_REJECTED,
                'reviewed_by_user_id' => $actor->id,
                'review_notes' => $reviewNotes,
                'reviewed_at' => now(),
            ]);

            if ($lockedListing->status !== PropertyListing::STATUS_PUBLISHED) {
                $lockedListing->update([
                    'status' => PropertyListing::STATUS_REJECTED,
                    'reviewed_by_user_id' => $actor->id,
                    'reviewed_at' => now(),
                ]);
            }

            $this->auditService->forModel(
                action: 'property_listing.rejected',
                model: $lockedListing,
                oldValues: [
                    'listing_status' => $oldListingStatus,
                    'version_status' => PropertyListingVersion::STATUS_PENDING,
                ],
                newValues: [
                    'listing_status' => $lockedListing->fresh()->status,
                    'version_id' => $version->id,
                    'version_status' => PropertyListingVersion::STATUS_REJECTED,
                    'review_notes' => $reviewNotes,
                ],
                actor: $actor,
                request: $request,
            );

            return $version->fresh();
        });
    }

    public function hasPendingVersion(PropertyListing $listing): bool
    {
        return $listing->versions()
            ->where('status', PropertyListingVersion::STATUS_PENDING)
            ->exists();
    }

    public function hasEditableRevision(PropertyListing $listing): bool
    {
        return $listing->versions()
            ->whereIn('status', [
                PropertyListingVersion::STATUS_DRAFT,
                PropertyListingVersion::STATUS_CHANGES_REQUESTED,
            ])
            ->exists();
    }

    private function pendingVersion(PropertyListing $listing): ?PropertyListingVersion
    {
        return $listing->versions()
            ->where('status', PropertyListingVersion::STATUS_PENDING)
            ->latest('version_number')
            ->first();
    }

    private function lockListing(PropertyListing $listing): PropertyListing
    {
        return PropertyListing::query()
            ->whereKey($listing->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function nextVersionNumber(PropertyListing $listing): int
    {
        return ((int) $listing->versions()->max('version_number')) + 1;
    }

    private function listingPayload(PropertyListing $listing): array
    {
        return Arr::only($listing->getAttributes(), self::EDITABLE_FIELDS);
    }

    private function auditListingValues(PropertyListing $listing): array
    {
        return Arr::only(
            $listing->getAttributes(),
            [
                ...self::EDITABLE_FIELDS,
                'status',
                'published_at',
                'reviewed_by_user_id',
                'reviewed_at',
            ],
        );
    }
}
