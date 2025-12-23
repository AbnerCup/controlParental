<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('grade_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->string('student_code', 64)->unique();
            $t->string('first_name', 120);
            $t->string('last_name', 160);
            $t->date('birth_date')->nullable();
            $t->enum('status', ['active', 'inactive', 'graduated', 'transferred'])->default('active');
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['user_id']); // 1:1 si el alumno tiene cuenta propia
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
