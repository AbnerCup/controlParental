<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('device_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('api_client_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->enum('event_type', ['check_in', 'check_out', 'panic', 'heartbeat']);
            $t->dateTime('occurred_at'); // UTC
            $t->dateTime('received_at'); // UTC
            $t->boolean('signature_valid')->default(false);
            $t->json('payload');
            $t->boolean('processed')->default(false);
            $t->string('error_message', 240)->nullable();
            $t->timestamps();

            $t->index(['school_id', 'received_at']);
            $t->index(['device_id', 'occurred_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('device_events');
    }
};
