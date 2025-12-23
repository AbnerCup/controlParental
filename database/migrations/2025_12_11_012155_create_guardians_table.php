<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->string('first_name', 120);
            $t->string('last_name', 160);
            $t->string('email', 160)->nullable()->unique();
            $t->string('phone', 40)->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
