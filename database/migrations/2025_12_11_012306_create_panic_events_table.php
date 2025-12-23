<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('panic_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('student_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate(); // botón físico
            $t->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate(); // si fue desde app autenticada
            $t->dateTime('triggered_at'); // UTC
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->enum('source', ['app', 'device'])->default('app');
            $t->enum('triggered_by_type', ['guardian', 'student'])->default('guardian');
            $t->string('note', 240)->nullable();
            $t->enum('priority', ['low', 'medium', 'high', 'critical'])->default('high');
            $t->boolean('resolved')->default(false);
            $t->dateTime('resolved_at')->nullable();
            $t->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->timestamps();

            $t->index(['school_id', 'triggered_at']);
            $t->index(['student_id', 'triggered_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('panic_events');
    }
};
