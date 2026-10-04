<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_due_items', function (Blueprint $table) {
            $table->dropForeign(['tenancy_id']);

            $table->foreign('tenancy_id')
                ->references('id')
                ->on('tenancies')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rent_due_items', function (Blueprint $table) {
            $table->dropForeign(['tenancy_id']);

            $table->foreign('tenancy_id')
                ->references('id')
                ->on('tenancies')
                ->cascadeOnDelete();
        });
    }
};
