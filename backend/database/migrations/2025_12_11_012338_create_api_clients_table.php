<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_clients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->string('name', 120);
            $t->enum('type', ['gateway', 'device', 'service'])->default('gateway');
            $t->string('key_id', 64)->unique();
            $t->string('key_secret_hash'); // guarda hash (no el secreto en claro)
            $t->boolean('active')->default(true);
            $t->dateTime('last_used_at')->nullable();
            $t->timestamps();

            $t->index(['school_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('api_clients');
    }
};
