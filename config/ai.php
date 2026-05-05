<?php

return [
    'enabled' => env('AI_ENABLED', false),
    'provider' => env('AI_PROVIDER', 'qwen'),
    'base_url' => rtrim(env('AI_BASE_URL', 'http://127.0.0.1:8000'), '/'),
    'endpoint' => env('AI_CHAT_ENDPOINT', '/v1/chat/completions'),
    'health_endpoint' => env('AI_HEALTH_ENDPOINT', '/v1/models'),
    'api_key' => env('AI_API_KEY', ''),
    'model' => env('AI_MODEL', 'qwen'),
    'timeout' => (int) env('AI_TIMEOUT', 120),
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 10),
    'queue' => env('AI_QUEUE', 'default'),
    'system_prompt' => env(
        'AI_SYSTEM_PROMPT',
        'You are Bellflow AI. Help draft, summarize, and improve contracts with concise, business-safe output.'
    ),
];
