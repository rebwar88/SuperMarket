<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('registers')) {
            Schema::create('registers', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('سندوقی سەرەکی 01');
                $table->string('code')->default('REG-01');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->string('user_id'); // پشتیگیری دەکات لە هەردوو UUID و ژمارەی ئاسایی
                $table->unsignedBigInteger('register_id')->nullable()->default(1);
                $table->timestamp('opened_at')->useCurrent();
                $table->timestamp('closed_at')->nullable();
                $table->decimal('opening_cash', 15, 2)->default(0);
                $table->decimal('closing_cash', 15, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('registers');
    }
};
