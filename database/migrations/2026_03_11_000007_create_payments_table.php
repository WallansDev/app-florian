<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            // pending | paid | late | disputed
            $table->string('status', 20)->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
