<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_management_agreements', function (Blueprint $table) {
            $table->string('agreement_number')->nullable()->unique()->after('id');
            $table->string('fee_billing_frequency', 30)->default('monthly')->after('management_fee_value');
            $table->boolean('auto_renew')->default(false)->after('fee_billing_frequency');
            $table->unsignedSmallInteger('renewal_notice_days')->default(30)->after('auto_renew');
            $table->string('agreement_document_path')->nullable()->after('included_services');
        });
    }

    public function down(): void
    {
        Schema::table('property_management_agreements', function (Blueprint $table) {
            $table->dropUnique(['agreement_number']);
            $table->dropColumn([
                'agreement_number',
                'fee_billing_frequency',
                'auto_renew',
                'renewal_notice_days',
                'agreement_document_path',
            ]);
        });
    }
};
