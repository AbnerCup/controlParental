<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('grade_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->unsignedTinyInteger('weekday');
            $t->time('start_time');
            $t->time('end_time');
            $t->unsignedSmallInteger('late_grace_minutes')->default(0);
            $t->date('effective_from');
            $t->date('effective_to')->nullable();

            $t->timestamps();
            $t->unique(['grade_id', 'weekday', 'effective_from']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('grade_schedules');
    }
};
