<?php
namespace App\Shop\Product\Controllers;

use App\Shop\Common\BaseShopController;
use App\Shop\Common\Services\TelegramService; // Use the new service
use Hleb\Static\Log; // Hleb's logging facade
use Hleb\Static\Settings; // To access config

class ProductController extends BaseShopController
{
    private TelegramService $telegramService;

    public function __construct()
    {
        $this->telegramService = new TelegramService();
    }

    // Product related actions (catalog, view, search)
    public function index() { return "Product Catalog"; }
    public function show(string $id) { return "Product Detail: " . $id; }
    public function search() { return "Search Results"; }
    public function listForMiniApp() { return json_encode(['products' => []]); }

    /**
     * Handles incoming webhook updates from Telegram.
     */
    public function handleWebhook()
    {
        if (!$this->telegramService->isInitialized()) {
            Log::error('Telegram Bot: Service not initialized in Controller.');
            return response('Bot not configured (service init failed).', 500);
        }

        $update = $this->telegramService->getWebhookUpdate();

        if ($update && $update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            if ($text === '/start') {
                $dynamicMiniAppBaseUrl = '';
                $sharedUrlFilePath = '/run/shared_config/localtunnel_url.txt';
                if (file_exists($sharedUrlFilePath) && is_readable($sharedUrlFilePath)) {
                    $fileContent = @file_get_contents($sharedUrlFilePath);
                    if ($fileContent !== false) {
                        $potentialUrl = trim($fileContent);
                        // Basic validation for a URL structure
                        if (filter_var($potentialUrl, FILTER_VALIDATE_URL) && (strpos($potentialUrl, 'http://') === 0 || strpos($potentialUrl, 'https://') === 0)) {
                            $dynamicMiniAppBaseUrl = $potentialUrl;
                            Log::info("Successfully read Mini App base URL from shared file: " . $dynamicMiniAppBaseUrl);
                        } else {
                            Log::warning("Content of shared URL file ('" . $sharedUrlFilePath . "') is not a valid URL: " . $fileContent);
                        }
                    } else {
                        Log::error("Could not read content from shared URL file: " . $sharedUrlFilePath);
                    }
                } else {
                    Log::info("Shared URL file not found or not readable: " . $sharedUrlFilePath . ". Falling back to .env config.");
                }

                $miniAppUrl = !empty($dynamicMiniAppBaseUrl) ? $dynamicMiniAppBaseUrl : config('telegram.mini_app_base_url');

                if (empty($miniAppUrl)) {
                    Log::warning('Mini App URL is not configured (neither in shared file nor .env). Cannot send WebApp button.');
                    $this->telegramService->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Бот работает! Добро пожаловать! (Mini App URL не настроен)'
                    ]);
                } else {
                    // Ensure no double slashes if $miniAppUrl might have a trailing slash
                    // and we are appending /shop_mini_app/
                    $finalMiniAppUrl = rtrim($miniAppUrl, '/') . '/shop_mini_app/';

                    $replyMarkup = [
                        'inline_keyboard' => [
                            [
                                ['text' => '🛍️ Открыть Магазин (Mini App)', 'web_app' => ['url' => $finalMiniAppUrl]]
                                // Assuming shop_mini_app/ is the public path to the mini app's index.html
                                // The full URL to mini_app/index.html should be formed correctly.
                                // If MINI_APP_BASE_URL in .env already includes the full path to index.html,
                                // then just use $miniAppUrl directly.
                                // For now, assuming MINI_APP_BASE_URL is the base (e.g. https://xyz.loca.lt)
                                // and the Mini App is at /shop_mini_app/ relative to that.
                            ]
                        ]
                    ];

                    $this->telegramService->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Бот работает! Добро пожаловать!',
                        'reply_markup' => json_encode($replyMarkup)
                    ]);
                }
            }
        } elseif ($update === null && $this->telegramService->isInitialized()) {
            // This case might mean getWebhookUpdate itself failed internally and logged it
            Log::warning('Received null update from TelegramService, but service is initialized.');
        }

        return response('OK', 200);
    }
}
