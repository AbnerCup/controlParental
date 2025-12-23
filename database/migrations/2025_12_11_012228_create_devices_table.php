<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->enum('type', ['RFID', 'NFC', 'QR', 'BLE'])->default('RFID');
            $t->string('uid', 128)->unique();
            $t->enum('status', ['active', 'lost', 'replaced', 'retired'])->default('active');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
