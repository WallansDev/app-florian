<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('role', 20)->default('client')->after('phone');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('api_token', 80)->unique()->nullable()->default(null)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'is_active', 'api_token']);
        });
    }
};
