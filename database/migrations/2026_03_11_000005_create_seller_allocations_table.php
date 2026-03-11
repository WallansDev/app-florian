<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->integer('allocated_qty')->default(0);
            $table->integer('remaining_qty')->default(0);
            $table->timestamps();

            $table->unique(['weekly_stock_id', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_allocations');
    }
};
