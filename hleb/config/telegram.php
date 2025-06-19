<?php
// Telegram Bot specific configurations

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_path' => env('TELEGRAM_WEBHOOK_PATH', '/telegram_bot_webhook'),
    // webhook_base_url will come from .env directly when setting the webhook with Telegram API

    // Optional: Bot username (can be fetched via API too)
    // 'bot_username' => env('TELEGRAM_BOT_USERNAME'),

    // Optional: Admin User IDs for bot administration
    'admin_ids' => array_filter(array_map('intval', explode(',', env('TELEGRAM_ADMIN_IDS', '')))),

    // Other bot-specific settings
];
