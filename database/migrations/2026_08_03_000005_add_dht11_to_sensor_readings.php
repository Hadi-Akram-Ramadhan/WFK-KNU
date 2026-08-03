<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->decimal('temperature_c', 4, 2)->nullable()->after('distance_cm');      // Suhu DHT11 (°C)
            $table->decimal('humidity_percent', 5, 2)->nullable()->after('temperature_c');  // Kelembapan DHT11 (%)
        });

        Schema::table('ai_analyses', function (Blueprint $table) {
            $table->integer('flood_probability_percent')->default(15)->after('risk_level'); // Probabilitas Banjir (%)
            $table->string('weather_condition')->nullable()->after('flood_probability_percent'); // Kondisi Cuaca / Kelembapan
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table) {
            $table->dropColumn(['temperature_c', 'humidity_percent']);
        });

        Schema::table('ai_analyses', function (Blueprint $table) {
            $table->dropColumn(['flood_probability_percent', 'weather_condition']);
        });
    }
};
