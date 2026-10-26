<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('reservations', 'quoted_price')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->decimal('quoted_price', 10, 2)->nullable()->after('selected_options');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reservations', 'quoted_price')) {
            Schema::table('reservations', fn (Blueprint $table) => $table->dropColumn('quoted_price'));
        }
    }
};
