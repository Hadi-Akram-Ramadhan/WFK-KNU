<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_node_id')->constrained()->onDelete('cascade');
            $table->foreignId('sensor_reading_id')->nullable()->constrained('sensor_readings')->nullOnDelete();
            $table->string('trigger');                         // 'danger_threshold' | 'periodic_summary'
            $table->text('prompt_sent');
            $table->text('ai_response');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->json('recommended_actions')->nullable();   // Array of action strings
            $table->string('model_used')->default('llama3.2:3b');
            $table->integer('response_time_ms')->nullable();   // Waktu Ollama response
            $table->timestamps();

            $table->index(['sensor_node_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
