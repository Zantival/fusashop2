<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'variants')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('variants')->nullable()->after('available_options');
            });
        }

        if (!Schema::hasColumn('cart_items', 'unit_price')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->decimal('unit_price', 10, 2)->nullable()->after('selected_options');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cart_items', 'unit_price')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->dropColumn('unit_price'));
        }
        if (Schema::hasColumn('products', 'variants')) {
            Schema::table('products', fn (Blueprint $table) => $table->dropColumn('variants'));
        }
    }
};
