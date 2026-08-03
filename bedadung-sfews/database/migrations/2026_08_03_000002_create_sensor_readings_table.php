<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_node_id')->constrained()->onDelete('cascade');
            $table->decimal('distance_cm', 6, 2);      // Jarak dari sensor ke permukaan air (cm)
            $table->decimal('water_level_m', 6, 3);    // Ketinggian air terkonversi (m)
            $table->enum('status', ['safe', 'caution', 'danger'])->default('safe');
            $table->decimal('rise_rate_cm_per_min', 8, 4)->nullable(); // Laju kenaikan air (cm/menit)
            $table->decimal('capacity_percent', 5, 2)->nullable();     // Persentase kapasitas sungai
            $table->timestamps();

            // Index untuk query time-series yang efisien
            $table->index(['sensor_node_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
