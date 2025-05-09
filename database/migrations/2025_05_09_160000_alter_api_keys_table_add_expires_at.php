<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('api_keys', 'expires_at')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('api_keys', 'expires_at')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
