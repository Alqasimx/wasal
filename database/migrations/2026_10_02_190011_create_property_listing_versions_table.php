<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve tables created by an earlier local build and record this migration safely.
        if (Schema::hasTable('property_listing_versions')) {
            return;
        }

        Schema::create('property_listing_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_listing_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->foreignId('submitted_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();

            $table->unique(['property_listing_id', 'version_number']);
            $table->index(['property_listing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_listing_versions');
    }
};
