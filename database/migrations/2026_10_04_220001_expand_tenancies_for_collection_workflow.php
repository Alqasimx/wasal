<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->unique()->after('id');
            $table->unsignedTinyInteger('due_day')->nullable()->after('payment_frequency');
            $table->unsignedSmallInteger('grace_days')->default(0)->after('due_day');
            $table->decimal('security_deposit', 18, 2)->default(0)->after('grace_days');
            $table->boolean('auto_generate_dues')->default(true)->after('security_deposit');
            $table->string('contract_attachment_path')->nullable()->after('auto_generate_dues');
            $table->text('notes')->nullable()->after('contract_attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('tenancies', function (Blueprint $table) {
            $table->dropUnique(['contract_number']);
            $table->dropColumn([
                'contract_number',
                'due_day',
                'grace_days',
                'security_deposit',
                'auto_generate_dues',
                'contract_attachment_path',
                'notes',
            ]);
        });
    }
};
