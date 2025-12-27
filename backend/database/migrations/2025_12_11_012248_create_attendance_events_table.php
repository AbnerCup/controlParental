<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('student_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->enum('event_type', ['check_in', 'check_out', 'manual_adjust', 'correction']);
            $t->enum('source', ['device', 'app', 'import'])->default('device');
            $t->dateTime('occurred_at'); // UTC
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->json('payload')->nullable();
            $t->timestamps();

            $t->index(['student_id', 'occurred_at']);
            $t->index(['school_id', 'occurred_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
