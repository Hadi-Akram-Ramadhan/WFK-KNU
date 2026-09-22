<?php

namespace App\Jobs;

use App\Models\SensorReading;
use App\Services\AICache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchAnalyzeFloodData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(public readonly array $readingIds) {}

    public function handle(): void
    {
        $readings = SensorReading::with('node')->whereIn('id', $this->readingIds)->get();

        if ($readings->isEmpty()) return;

        foreach ($readings as $reading) {
            if (!$reading->node) continue;

            // Dispatch individual analysis jobs dengan delay untuk throttle
            AnalyzeFloodDataWithAI::dispatch($reading, 'batch_analysis')
                ->delay(now()->addSeconds(rand(1, 5)));
        }

        Log::info("[BatchAI] Dispatched {$readings->count()} analysis jobs");
    }
}
