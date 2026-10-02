<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->string('listing_number')->unique();
            $table->string('purpose', 20);
            $table->decimal('price', 18, 2);
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->string('price_period')->nullable();
            $table->string('public_title');
            $table->text('public_description')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('share_token', 64)->unique()->nullable();
            $table->boolean('share_enabled')->default(true);
            $table->unsignedBigInteger('share_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'purpose']);
            $table->index(['published_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_listings');
    }
};
