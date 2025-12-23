<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('guardian_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->enum('relationship', ['mother', 'father', 'tutor', 'other'])->default('tutor');
            $t->boolean('is_primary')->default(false);
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->timestamps();
            $t->unique(['student_id', 'guardian_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
