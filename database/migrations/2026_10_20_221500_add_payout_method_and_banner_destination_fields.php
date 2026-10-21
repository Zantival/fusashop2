<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_profiles') && !Schema::hasColumn('company_profiles', 'payout_method_type')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->string('payout_method_type', 30)->nullable()->after('wompi_events_secret');
            });
        }

        if (Schema::hasTable('banner_requests')) {
            Schema::table('banner_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('banner_requests', 'payout_destination_mode')) {
                    $table->string('payout_destination_mode', 30)->default('corporate')->after('cost');
                }
                if (!Schema::hasColumn('banner_requests', 'payout_method_type')) {
                    $table->string('payout_method_type', 30)->nullable()->after('payout_destination_mode');
                }
                if (!Schema::hasColumn('banner_requests', 'payout_bank_name')) {
                    $table->string('payout_bank_name', 120)->nullable()->after('payout_method_type');
                }
                if (!Schema::hasColumn('banner_requests', 'payout_account_type')) {
                    $table->string('payout_account_type', 50)->nullable()->after('payout_bank_name');
                }
                if (!Schema::hasColumn('banner_requests', 'payout_account_number')) {
                    $table->string('payout_account_number', 60)->nullable()->after('payout_account_type');
                }
                if (!Schema::hasColumn('banner_requests', 'payout_account_holder')) {
                    $table->string('payout_account_holder', 150)->nullable()->after('payout_account_number');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('company_profiles') && Schema::hasColumn('company_profiles', 'payout_method_type')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->dropColumn('payout_method_type');
            });
        }

        if (Schema::hasTable('banner_requests')) {
            Schema::table('banner_requests', function (Blueprint $table) {
                $cols = array_filter([
                    'payout_destination_mode',
                    'payout_method_type',
                    'payout_bank_name',
                    'payout_account_type',
                    'payout_account_number',
                    'payout_account_holder',
                ], fn ($c) => Schema::hasColumn('banner_requests', $c));
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
