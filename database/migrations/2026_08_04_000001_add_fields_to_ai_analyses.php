<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_analyses', 'flood_probability_percent')) {
                $table->integer('flood_probability_percent')->nullable()->after('risk_level');
            }
            if (!Schema::hasColumn('ai_analyses', 'weather_condition')) {
                $table->string('weather_condition')->nullable()->after('flood_probability_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('ai_analyses', 'flood_probability_percent')) {
                $table->dropColumn('flood_probability_percent');
            }
            if (Schema::hasColumn('ai_analyses', 'weather_condition')) {
                $table->dropColumn('weather_condition');
            }
        });
    }
};
