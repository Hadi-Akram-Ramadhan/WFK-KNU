<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AICache
{
    private const TTL = 300; // 5 minutes

    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember("ai:{$key}", $ttl ?? self::TTL, $callback);
    }

    public static function forget(string $key): void
    {
        Cache::forget("ai:{$key}");
    }

    public static function flushNode(int $nodeId): void
    {
        Cache::forget("ai:node:{$nodeId}:recent");
    }
}
