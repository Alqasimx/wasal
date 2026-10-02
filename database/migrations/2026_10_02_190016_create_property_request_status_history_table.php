<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL limits identifier names; this table may also exist after a partial migration.
        if (Schema::hasTable('property_request_status_history')) {
            return;
        }

        Schema::create('property_request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_request_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['property_request_id', 'created_at'], 'prsh_request_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_request_status_history');
    }
};
