<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->string('name', 80);
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->timestamps();
            $t->unique(['school_id', 'name']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
