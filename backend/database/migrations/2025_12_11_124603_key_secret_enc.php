<?php
// database/migrations/2025_12_11_000001_add_key_secret_enc_to_api_clients.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('api_clients', function (Blueprint $t) {
            if (!Schema::hasColumn('api_clients', 'key_secret_enc')) {
                $t->text('key_secret_enc')->nullable()->after('key_secret_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('api_clients', function (Blueprint $t) {
            if (Schema::hasColumn('api_clients', 'key_secret_enc')) {
                $t->dropColumn('key_secret_enc');
            }
        });
    }
};
