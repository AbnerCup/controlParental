<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_calendar_days', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->date('day');
            $t->enum('day_type', ['class', 'holiday', 'closed', 'strike', 'maintenance'])->default('class');
            $t->string('note', 240)->nullable();
            $t->timestamps();
            $t->unique(['school_id', 'day']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('school_calendar_days');
    }
};
