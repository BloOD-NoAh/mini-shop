<?php

return [
    // Default provider (admin can override via settings)
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),

    // Provider-specific default models (chosen for Malaysia availability and cost)
    'providers' => [
        'gemini' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'default_model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
            'available_models' => [
                'gemini-1.5-flash',
                'gemini-1.5-pro',
                'gemini-2.5-flash',
            ],
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'default_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'available_models' => [
                'gpt-4o-mini',
                'gpt-4o',
                'gpt-3.5-turbo',
            ],
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'default_model' => env('ANTHROPIC_MODEL', 'claude-3-5-haiku-20241022'),
            'available_models' => [
                'claude-3-5-haiku-20241022',
                'claude-3-5-sonnet-20241022',
            ],
        ],
    ],
];

