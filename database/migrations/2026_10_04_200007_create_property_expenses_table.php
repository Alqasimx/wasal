<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('property_management_agreement_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('property_vendor_id')->nullable()->constrained('property_vendors')->nullOnDelete();
            $table->string('category', 40);
            $table->string('description');
            $table->decimal('amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->date('incurred_at');
            $table->string('cost_bearer', 20)->default('owner');
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('reference_number')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'incurred_at']);
            $table->index(['payment_status', 'incurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_expenses');
    }
};
