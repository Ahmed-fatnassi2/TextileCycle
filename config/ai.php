<?php

return [
    'repair_enabled' => env('AI_REPAIR_ENABLED', true),
    'daily_limits' => [
        'chatbot' => (int) env('AI_REPAIR_CHATBOT_DAILY_LIMIT', 20),
        'message' => (int) env('AI_REPAIR_MESSAGE_DAILY_LIMIT', 30),
        'upcycle' => (int) env('AI_REPAIR_UPCYCLE_DAILY_LIMIT', 10),
        'quality' => (int) env('AI_REPAIR_QUALITY_DAILY_LIMIT', 10),
    ],
    'global_daily_limit' => (int) env('AI_REPAIR_GLOBAL_DAILY_LIMIT', 300),
    'max_tokens' => ['chatbot' => 400, 'message' => 300, 'upcycle' => 400, 'quality' => 400],
    'cache_hours' => ['chatbot' => 1, 'message' => 24, 'upcycle' => 24, 'quality' => 24],
    'default_cost_range' => ['min' => 15, 'max' => 80],
    'upcycle_cost_threshold' => (float) env('AI_REPAIR_UPCYCLE_COST_THRESHOLD', 80),
];
