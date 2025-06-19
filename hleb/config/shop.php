<?php
// Default shop configurations
// Settings here can be overridden by environment variables (e.g., in .env)
// or in a shop-local.php file if Hleb's config system supports it.

return [
    'currency' => env('SHOP_CURRENCY', 'RUB'), // Default currency
    'items_per_page' => env('SHOP_ITEMS_PER_PAGE', 12),

    // Example: Payment gateway settings (placeholders)
    'payment_gateways' => [
        'default' => env('SHOP_DEFAULT_PAYMENT_GATEWAY', 'test_gateway'),
        'test_gateway' => [
            'api_key' => env('TEST_GATEWAY_API_KEY'),
            'secret_key' => env('TEST_GATEWAY_SECRET_KEY'),
            'endpoint' => env('TEST_GATEWAY_ENDPOINT'),
        ],
        // Add other payment gateways here
    ],

    // Example: AI Service settings
    'ai_service' => [
        'endpoint' => env('AI_SERVICE_ENDPOINT'),
        'api_key' => env('AI_SERVICE_API_KEY'),
    ],

    // Add other shop-specific configurations here
];
