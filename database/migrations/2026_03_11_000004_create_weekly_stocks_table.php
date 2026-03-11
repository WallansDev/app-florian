<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('week_start'); // lundi de la semaine
            $table->integer('total_qty')->default(0);
            $table->integer('available_qty')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['supplier_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_stocks');
    }
};
