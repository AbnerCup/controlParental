<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name', 160);
            $t->string('email', 160)->unique();
            $t->string('password');
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
