<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->string('user_id');
            $table->string('gateway'); // fib, fastpay, pos_card, manual_pos
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('IQD');
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->string('reference_no')->nullable(); // بۆ مامەڵەی دەستی ئامێر یان RRN
            $table->string('gateway_transaction_id')->nullable(); // ID ـی گەڕاوەی API
            $table->text('qr_code_data')->nullable(); // کاتی بەکارهێنانی بارکۆدی داینامیک
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
