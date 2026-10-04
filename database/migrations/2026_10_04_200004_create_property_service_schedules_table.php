<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_service_id')->constrained('property_services')->restrictOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('property_vendor_id')->nullable()->constrained('property_vendors')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('frequency', 30);
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->date('starts_at');
            $table->dateTime('next_due_at');
            $table->dateTime('last_generated_due_at')->nullable();
            $table->dateTime('last_completed_at')->nullable();
            $table->unsignedInteger('notify_before_minutes')->default(1440);
            $table->decimal('estimated_cost', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_due_at']);
            $table->index(['property_id', 'property_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_service_schedules');
    }
};
