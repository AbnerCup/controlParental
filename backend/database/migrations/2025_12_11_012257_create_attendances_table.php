<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('student_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->date('class_date'); // fecha académica (local)
            $t->dateTime('check_in_at')->nullable();   // UTC
            $t->dateTime('check_out_at')->nullable();  // UTC
            $t->enum('status', ['present', 'late', 'absent', 'left_early'])->default('present');
            $t->enum('method', ['device_scan', 'manual', 'import'])->nullable();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->timestamps();

            $t->unique(['student_id', 'class_date']);
            $t->index(['school_id', 'class_date']);
            $t->index(['school_id', 'class_date', 'status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
