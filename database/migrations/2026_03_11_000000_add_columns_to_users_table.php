<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('client')->after('phone');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (! Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->unique()->nullable()->default(null)->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = collect(['phone', 'role', 'is_active', 'api_token'])
                ->filter(fn($col) => Schema::hasColumn('users', $col))
                ->values()
                ->all();

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
