<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('tenancies', function (Blueprint $table) { $table->id(); $table->foreignId('property_unit_id')->constrained()->restrictOnDelete(); $table->foreignId('tenant_id')->constrained()->restrictOnDelete(); $table->date('starts_at'); $table->date('ends_at')->nullable(); $table->decimal('rent_amount', 18, 2); $table->foreignId('currency_id')->constrained()->restrictOnDelete(); $table->string('payment_frequency', 20)->default('monthly'); $table->string('status', 20)->default('active'); $table->timestamps(); $table->index(['property_unit_id','status']); }); } public function down(): void { Schema::dropIfExists('tenancies'); } };
