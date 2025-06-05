<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AgentQL API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AgentQL AI-powered web scraping service
    |
    */

    'api_key' => env('AGENTQL_API_KEY'),
    'base_url' => env('AGENTQL_BASE_URL', 'https://api.agentql.com/v1'),
    'timeout' => env('AGENTQL_TIMEOUT', 60),
    'default_mode' => env('AGENTQL_MODE', 'fast'), // 'fast' or 'standard'
    'scroll_enabled' => env('AGENTQL_SCROLL_ENABLED', true),
    'wait_time' => env('AGENTQL_WAIT_TIME', 3),
];
