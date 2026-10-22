<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El pedido existente es la orden general pagada en una sola transacción Wompi.
        // Una suborden hija por vendedor, creada al confirmarse el pago general.
        Schema::create('merchant_suborders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained('users')->restrictOnDelete();
            $table->string('reference', 80)->unique();
            $table->json('items_snapshot');
            $table->unsignedBigInteger('gross_cents');
            $table->unsignedBigInteger('commission_cents');
            $table->unsignedBigInteger('net_cents');
            $table->string('currency', 3)->default('COP');
            $table->string('status', 20)->default('processing');
            $table->string('payout_status', 20)->default('available'); // available | paid | reversed
            $table->timestamp('paid_at');
            $table->timestamps();
            $table->unique(['order_id', 'merchant_id']);
            $table->index(['merchant_id', 'payout_status', 'created_at']);
        });

        // Libro mayor inmutable. amount_cents siempre positivo; direction define crédito o débito.
        Schema::create('merchant_wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('suborder_id')->nullable()->constrained('merchant_suborders')->nullOnDelete();
            $table->string('entry_type', 30); // sale_credit | payout_debit | reversal
            $table->string('direction', 10); // credit | debit
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3)->default('COP');
            $table->string('idempotency_key', 120)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('posted_at');
            $table->timestamps();
            $table->index(['merchant_id', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_wallet_entries');
        Schema::dropIfExists('merchant_suborders');
    }
};
