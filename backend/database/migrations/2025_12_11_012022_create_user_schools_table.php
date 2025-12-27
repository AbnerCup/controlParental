<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_schools', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $t->timestamps();
            $t->unique(['user_id', 'school_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('user_schools');
    }
};
