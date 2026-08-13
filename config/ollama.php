<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | URL ke Ollama REST API yang berjalan di VPS.
    | Mendukung env var OLLAMA_URL atau OLLAMA_HOST (keduanya diterima).
    | Default: http://localhost:11434
    |
    */
    'url'     => env('OLLAMA_URL', env('OLLAMA_HOST', 'http://localhost:11434')),
    'model'   => env('OLLAMA_MODEL', 'qwen2.5:1.5b'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
];

