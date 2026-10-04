<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number')->unique();
            $table->foreignId('property_management_agreement_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_owner_id')->constrained('property_owners')->restrictOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->decimal('gross_collections', 18, 2)->default(0);
            $table->decimal('owner_expenses', 18, 2)->default(0);
            $table->decimal('management_fee', 18, 2)->default(0);
            $table->decimal('net_payable', 18, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique([
                'property_management_agreement_id',
                'period_start',
                'period_end',
                'currency_id',
            ], 'owner_settlement_period_unique');

            $table->index(['status', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_settlements');
    }
};
