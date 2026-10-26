<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner_requests', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('payment_transaction_id')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_transaction_id');
            $table->string('refund_status')->default('not_required')->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('banner_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_transaction_id', 'paid_at', 'refund_status']);
        });
    }
};
