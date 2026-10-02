<?php

namespace App\Filament\Resources\PropertyListings\Pages;

use App\Filament\Pages\AuditedEditRecord;
use App\Filament\Resources\PropertyListings\PropertyListingResource;
use App\Models\PropertyListing;
use App\Models\PropertyListingVersion;
use App\Services\PropertyListingWorkflowService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPropertyListing extends AuditedEditRecord
{
    protected static string $resource = PropertyListingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->status !== PropertyListing::STATUS_PUBLISHED) {
            return $data;
        }

        $editableVersion = $this->record->versions()
            ->whereIn('status', [
                PropertyListingVersion::STATUS_DRAFT,
                PropertyListingVersion::STATUS_CHANGES_REQUESTED,
            ])
            ->latest('version_number')
            ->first();

        if (! $editableVersion) {
            return $data;
        }

        return array_replace(
            $data,
            $editableVersion->payload ?? [],
        );
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var PropertyListing $record */
        $workflow = app(PropertyListingWorkflowService::class);

        if ($record->status === PropertyListing::STATUS_PENDING_REVIEW) {
            throw ValidationException::withMessages([
                'public_title' => 'لا يمكن تعديل الإعلان أثناء وجوده قيد المراجعة.',
            ]);
        }

        if (
            $record->status === PropertyListing::STATUS_PUBLISHED
            && $workflow->hasPendingVersion($record)
        ) {
            throw ValidationException::withMessages([
                'public_title' => 'يوجد تعديل قيد المراجعة لهذا الإعلان. انتظر نتيجة المراجعة قبل إجراء تعديل جديد.',
            ]);
        }

        if ($record->status === PropertyListing::STATUS_PUBLISHED) {
            $workflow->draftPublishedChanges(
                listing: $record,
                data: $data,
                actor: auth()->user(),
                request: request(),
            );

            Notification::make()
                ->success()
                ->title('تم حفظ التعديل كمسودة')
                ->body('بقي الإعلان المنشور دون تغيير. أرسل المسودة للمراجعة عندما تصبح جاهزة.')
                ->send();

            return $record->refresh();
        }

        return parent::handleRecordUpdate($record, $data);
    }
}
