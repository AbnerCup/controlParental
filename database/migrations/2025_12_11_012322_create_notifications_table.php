<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $t->enum('channel', ['email', 'sms', 'whatsapp', 'push']);
            $t->string('template_key', 80);
            $t->json('payload')->nullable();
            $t->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $t->string('error_message', 240)->nullable();
            $t->enum('related_type', ['attendance', 'panic_event', 'device_event', 'generic'])->default('generic');
            $t->unsignedBigInteger('related_id')->nullable();
            $t->dateTime('queued_at'); // UTC
            $t->dateTime('sent_at')->nullable(); // UTC
            $t->timestamps();

            $t->index(['status', 'queued_at']);
            $t->index(['related_type', 'related_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
