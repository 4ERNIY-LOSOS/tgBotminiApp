<?php
declare(strict_types=1);

namespace App\Shop\Product\Controllers;

use Hleb\Base\Controller; // Базовый контроллер Hleb
// use App\Shop\Common\Services\TelegramService; // Не используется для listForMiniApp в данный момент
// use App\Shop\Common\Services\TelegramUpdateHandlerService; // Не используется для listForMiniApp в данный момент
use Hleb\Static\Log; // Фасад логирования Hleb, может понадобиться позже
use Hleb\Constructor\Data\JsonResponse; // Для ответа в формате JSON

/**
 * Контроллер для управления продуктами (товарами).
 * Class ProductController
 * @package App\Shop\Product\Controllers
 */
class ProductController extends Controller // Изменен родительский класс на базовый контроллер Hleb
{
    // private TelegramService $telegramService; // Удалено, так как связано с обработкой веб-хуков

    // Конструктор, связанный с веб-хуками, удален, так как это сейчас не основной фокус
    /*
    public function __construct()
    {
        $this->telegramService = new TelegramService();
    }
    */

    // Действия, связанные с продуктами (каталог, просмотр, поиск)
    // Это заглушки, которые будут реализованы по мере необходимости.
    public function index() { return "Представление каталога продуктов (Заглушка)"; }
    public function show(string $id) { return "Представление детальной информации о продукте ID: " . $id . " (Заглушка)"; }
    public function search() { return "Представление результатов поиска (Заглушка)"; }

    /**
     * Возвращает список продуктов для Mini App.
     * GET /api/miniapp/v1/products
     * @return JsonResponse
     */
    public function listForMiniApp(): JsonResponse
    {
        // Данные-заглушки - в реальном приложении они будут поступать из сервиса/базы данных
        $products = [
            [
                'id' => 1,
                'name' => 'Товар 1',
                'price' => 1000,
                'description' => 'Описание товара 1',
                'image_url' => 'https://via.placeholder.com/150/0000FF/FFFFFF?Text=Product1'
            ],
            [
                'id' => 2,
                'name' => 'Товар 2',
                'price' => 1500,
                'description' => 'Описание товара 2',
                'image_url' => 'https://via.placeholder.com/150/FF0000/FFFFFF?Text=Product2'
            ],
            [
                'id' => 3,
                'name' => 'Товар 3 (без описания)',
                'price' => 750,
                'image_url' => 'https://via.placeholder.com/150/00FF00/FFFFFF?Text=Product3'
            ],
        ];

        return new JsonResponse($products);
    }

    /**
     * Обрабатывает входящие обновления веб-хуков от Telegram.
     * Этот метод в основном будет делегировать обработку TelegramUpdateHandlerService.
     * ПРИМЕЧАНИЕ: Этот проект использует long polling, поэтому этот обработчик веб-хуков не используется активно сервисом bot-poller.
     */
    public function handleWebhook()
    {
        // Временно убедимся, что TelegramService доступен, если этот метод когда-либо будет вызван напрямую.
        // Рассмотрите возможность удаления или рефакторинга, если веб-хуки точно не используются.
        $telegramService = new \App\Shop\Common\Services\TelegramService();

        if (!$telegramService->isInitialized()) {
            Log::error('Бот Telegram: TelegramService не инициализирован в веб-хуке ProductController.');
            return response('Бот (TelegramService) не настроен.', 500);
        }

        $update = $telegramService->getWebhookUpdate();

        if ($update) {
            $updateHandler = new \App\Shop\Common\Services\TelegramUpdateHandlerService($telegramService);
            try {
                $updateHandler->processUpdate($update);
            } catch (\Throwable $e) {
                Log::error("Ошибка в TelegramUpdateHandlerService через веб-хук: " . $e->getMessage()); // Упрощенная трассировка
            }
        } elseif ($telegramService->isInitialized()) {
            Log::warning('ProductController::handleWebhook не получил действительного обновления от TelegramService.');
        } else {
            Log::error('ProductController::handleWebhook вызван, но TelegramService не инициализирован и обновление не получено.');
        }

        return response('OK', 200);
    }
}
```
