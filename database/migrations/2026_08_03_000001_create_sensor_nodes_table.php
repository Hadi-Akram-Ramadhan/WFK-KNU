<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_id')->unique(); // e.g. "BEDADUNG_01"
            $table->string('name');              // e.g. "Checkpoint Alpha - Sumbersari"
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('status', ['online', 'offline', 'warning'])->default('offline');
            $table->decimal('sensor_height_cm', 6, 2)->default(30.0); // Tinggi sensor dari permukaan air saat normal
            $table->timestamp('last_seen')->nullable();
            $table->string('api_token')->nullable(); // Token untuk autentikasi Wemos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_nodes');
    }
};
