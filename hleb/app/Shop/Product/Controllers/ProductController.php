<?php
namespace App\Shop\Product\Controllers;

use App\Shop\Common\BaseShopController;
use App\Shop\Common\Services\TelegramService; // Use the new service
use App\Shop\Common\Services\TelegramUpdateHandlerService;
use Hleb\Static\Log; // Hleb's logging facade
// Settings class might not be needed anymore if config() is used directly and no other settings are read.
// use Hleb\Static\Settings;

class ProductController extends BaseShopController
{
    private TelegramService $telegramService;

    public function __construct()
    {
        // It's good practice to ensure TelegramService is available.
        // Consider dependency injection if the framework supports it easily,
        // otherwise, direct instantiation is fine for Hleb modules.
        $this->telegramService = new TelegramService();
    }

    // Product related actions (catalog, view, search)
    // These are placeholders and would be implemented as needed.
    public function index() { return "Product Catalog View (Placeholder)"; }
    public function show(string $id) { return "Product Detail View for ID: " . $id . " (Placeholder)"; }
    public function search() { return "Search Results View (Placeholder)"; }

    // Example API endpoint for Mini App
    public function listForMiniApp() {
        // In a real app, this would fetch products from a ProductService
        $products = [
            ['id' => 1, 'name' => 'Товар 1', 'price' => 1000],
            ['id' => 2, 'name' => 'Товар 2', 'price' => 1500],
        ];
        return json_encode(['products' => $products]);
    }

    /**
     * Handles incoming webhook updates from Telegram.
     * This method will primarily delegate processing to TelegramUpdateHandlerService.
     */
    public function handleWebhook()
    {
        if (!$this->telegramService->isInitialized()) {
            Log::error('Telegram Bot: TelegramService not initialized in ProductController.');
            return response('Bot (TelegramService) not configured.', 500);
        }

        $update = $this->telegramService->getWebhookUpdate();

        if ($update) {
            // Assumes 'use App\Shop\Common\Services\TelegramUpdateHandlerService;' is present.
            $updateHandler = new TelegramUpdateHandlerService($this->telegramService);
            try {
                // We assume $update is the correct object type (e.g., \Telegram\Bot\Objects\Update)
                // as provided by $this->telegramService->getWebhookUpdate()
                $updateHandler->processUpdate($update);
            } catch (\Throwable $e) {
                Log::error("Error in TelegramUpdateHandlerService: " . $e->getMessage()); // Simplified trace
            }
        } elseif ($this->telegramService->isInitialized()) {
            Log::warning('ProductController::handleWebhook received no valid update from TelegramService.');
        } else {
            Log::error('ProductController::handleWebhook called but TelegramService is not initialized and no update obtained.');
        }

        return response('OK', 200);
    }
}
```
