<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('accepted_privacy_at')->nullable();
            $table->string('accepted_terms_version', 20)->nullable();
            $table->string('accepted_privacy_version', 20)->nullable();
            $table->timestamp('age_or_guardian_confirmed_at')->nullable();
            $table->timestamp('marketing_consent_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['marketing_consent_at']);
            $table->dropColumn([
                'accepted_privacy_at',
                'accepted_terms_version',
                'accepted_privacy_version',
                'age_or_guardian_confirmed_at',
                'marketing_consent_at',
            ]);
        });
    }
};
