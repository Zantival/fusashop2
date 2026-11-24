<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'id_document_path')) {
                $table->string('id_document_path', 500)->nullable()->after('camara_comercio_path');
            }
            if (!Schema::hasColumn('company_profiles', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('kyc_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('company_profiles', 'id_document_path')) {
                $columns[] = 'id_document_path';
            }
            if (Schema::hasColumn('company_profiles', 'is_verified')) {
                $columns[] = 'is_verified';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
