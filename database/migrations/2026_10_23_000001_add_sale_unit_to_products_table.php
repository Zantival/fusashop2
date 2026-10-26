<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Unidad en la que se vende y se cuenta el stock (unidad, g, 100 g, kg, ml, caja...).
            if (!Schema::hasColumn('products', 'sale_unit')) {
                $table->string('sale_unit', 30)->nullable()->after('stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sale_unit')) {
                $table->dropColumn('sale_unit');
            }
        });
    }
};
