<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->string('name', 160);
            $t->string('code', 64)->unique();
            $t->string('timezone', 64)->default('America/La_Paz');
            $t->string('city', 120)->nullable();
            $t->string('address', 240)->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
