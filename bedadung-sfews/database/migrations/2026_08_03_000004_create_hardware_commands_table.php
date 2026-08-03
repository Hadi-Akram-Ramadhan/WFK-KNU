<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hardware_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_node_id')->constrained()->onDelete('cascade');
            $table->enum('command_type', ['servo', 'siren', 'automated_mode']);
            $table->json('payload');                    // e.g. {"angle": 90} or {"active": true}
            $table->enum('source', ['ai', 'manual', 'threshold'])->default('threshold');
            $table->boolean('executed')->default(false);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['sensor_node_id', 'executed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hardware_commands');
    }
};
