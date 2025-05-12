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
        if (! Schema::hasColumn('api_keys', 'last_key_chars')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->string('last_key_chars')->nullable()->after('expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('api_keys', 'last_key_chars')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->dropColumn('last_key_chars');
            });
        }
    }
};
