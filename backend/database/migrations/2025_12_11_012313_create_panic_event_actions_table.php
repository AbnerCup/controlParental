<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('panic_event_actions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('panic_event_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $t->enum('action_type', ['ack', 'dispatch', 'contact_parent', 'contact_school', 'resolve']);
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $t->string('note', 240)->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['panic_event_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('panic_event_actions');
    }
};
