<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('tenancy_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('inspection_type', 30);
            $table->string('status', 20)->default('scheduled');
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('inspector_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('condition_score')->nullable();
            $table->json('findings')->nullable();
            $table->json('attachments')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['property_id', 'scheduled_at']);
        });

        Schema::create('utility_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('meter_type', 30);
            $table->string('meter_number');
            $table->string('unit_of_measure', 20)->default('unit');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'meter_number']);
        });

        Schema::create('utility_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_meter_id')->constrained()->restrictOnDelete();
            $table->dateTime('reading_at');
            $table->decimal('reading_value', 18, 3);
            $table->decimal('previous_value', 18, 3)->nullable();
            $table->decimal('consumption', 18, 3)->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['utility_meter_id', 'reading_at']);
        });

        Schema::create('property_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('tenancy_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('property_management_agreement_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('document_type', 40);
            $table->string('file_path')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['property_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_documents');
        Schema::dropIfExists('utility_meter_readings');
        Schema::dropIfExists('utility_meters');
        Schema::dropIfExists('property_inspections');
    }
};
