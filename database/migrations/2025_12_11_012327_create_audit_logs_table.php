<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->string('action', 80);
            $t->string('entity_type', 80);
            $t->unsignedBigInteger('entity_id')->nullable();
            $t->json('changes')->nullable();
            $t->string('ip', 64)->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['school_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
