<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('device_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('student_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->dateTime('assigned_at');   // UTC
            $t->dateTime('unassigned_at')->nullable(); // UTC
            $t->string('note', 240)->nullable();
            $t->timestamps();

            $t->unique(['device_id', 'student_id', 'assigned_at']);
            $t->index(['device_id', 'unassigned_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('device_assignments');
    }
};
