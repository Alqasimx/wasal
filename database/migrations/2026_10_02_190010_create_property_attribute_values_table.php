<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some local installations created this table before the migration was committed.
        // Preserve that data and let Laravel record this migration as completed.
        if (Schema::hasTable('property_attribute_values')) {
            return;
        }

        Schema::create('property_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('attributable_type');
            $table->unsignedBigInteger('attributable_id');
            $table->foreignId('property_feature_id')->constrained()->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 18, 6)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->unique(['attributable_type', 'attributable_id', 'property_feature_id'], 'attribute_values_unique');
            $table->index(['attributable_type', 'attributable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_attribute_values');
    }
};
