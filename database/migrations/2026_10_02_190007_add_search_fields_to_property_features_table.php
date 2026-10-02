<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_features', function (Blueprint $table) {
            $table->string('key')->nullable()->unique()->after('slug');
            $table->boolean('is_searchable')->default(false)->after('is_filterable');
        });
    }

    public function down(): void
    {
        Schema::table('property_features', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn(['key', 'is_searchable']);
        });
    }
};
