<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('panic_event_rate_limits', function (Blueprint $t) {
            $t->id();
            $t->enum('actor_type', ['guardian', 'student']); // quién dispara
            $t->unsignedBigInteger('actor_id');             // guardian_id o student_id
            $t->unsignedInteger('count')->default(0);
            $t->dateTime('window_start'); // inicio ventana (UTC), ej. 5 min
            $t->timestamps();

            $t->unique(['actor_type', 'actor_id', 'window_start']);
            $t->index(['actor_type', 'actor_id', 'window_start']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('panic_event_rate_limits');
    }
};
