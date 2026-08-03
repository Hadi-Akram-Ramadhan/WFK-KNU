<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ollama Configuration
    |--------------------------------------------------------------------------
    |
    | URL ke Ollama REST API yang berjalan di VPS.
    | Default: http://localhost:11434
    |
    */
    'url'     => env('OLLAMA_URL', 'http://localhost:11434'),
    'model'   => env('OLLAMA_MODEL', 'llama3.2:3b'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 60),
];
