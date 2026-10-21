<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('wompi_public_key', 120)->nullable()->after('whatsapp');
            $table->string('wompi_integrity_secret', 120)->nullable()->after('wompi_public_key');
            $table->string('wompi_events_secret', 120)->nullable()->after('wompi_integrity_secret');
            $table->string('payout_bank_name', 100)->nullable()->after('wompi_events_secret');
            $table->string('payout_account_type', 40)->nullable()->after('payout_bank_name'); // Ahorros, Corriente, Nequi, Daviplata
            $table->string('payout_account_number', 60)->nullable()->after('payout_account_type');
            $table->string('payout_account_holder', 150)->nullable()->after('payout_account_number');
            $table->string('payout_id_number', 40)->nullable()->after('payout_account_holder');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('merchant_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'wompi_public_key',
                'wompi_integrity_secret',
                'wompi_events_secret',
                'payout_bank_name',
                'payout_account_type',
                'payout_account_number',
                'payout_account_holder',
                'payout_id_number',
            ]);
        });
    }
};
