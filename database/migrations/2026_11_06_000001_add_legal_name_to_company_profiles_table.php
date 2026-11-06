<?php

use App\Models\CompanyProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('company_profiles', 'legal_name')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->string('legal_name')->nullable()->after('company_name');
            });
        }

        if (Schema::hasTable('company_profiles') && Schema::hasColumn('company_profiles', 'legal_name')) {
            DB::table('company_profiles')
                ->whereNull('legal_name')
                ->whereNotNull('company_name')
                ->orderBy('id')
                ->get(['id', 'company_name'])
                ->each(function ($row) {
                    $name = trim((string) $row->company_name);
                    if ($name !== '') {
                        DB::table('company_profiles')
                            ->where('id', $row->id)
                            ->update([
                                'legal_name' => CompanyProfile::suggestedLegalName($name),
                            ]);
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_profiles', 'legal_name')) {
            Schema::table('company_profiles', function (Blueprint $table) {
                $table->dropColumn('legal_name');
            });
        }
    }
};
