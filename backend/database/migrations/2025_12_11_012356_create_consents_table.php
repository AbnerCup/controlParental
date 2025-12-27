<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('guardian_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('student_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->string('policy_version', 32);
            $t->dateTime('consented_at'); // UTC
            $t->dateTime('revoked_at')->nullable(); // UTC
            $t->timestamps();

            $t->unique(['guardian_id', 'student_id', 'policy_version']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
