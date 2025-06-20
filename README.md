# Руководство по Разработке Интернет-Магазина в Telegram на Hleb

## Установка и Настройка

Это руководство поможет вам настроить и запустить проект локально для разработки. Мы будем использовать Docker для запуска приложения и `localtunnel` для получения публичного URL, необходимого для работы Telegram бота.

### Предварительные Требования
*   Установленный [Git](https://git-scm.com/).
*   Установленные [Docker](https://www.docker.com/products/docker-desktop/) и Docker Compose.
*   Установленный [Node.js и npm](https://nodejs.org/) (для установки `localtunnel`).

### Шаг 1: Клонирование Репозитория
1.  Откройте терминал или командную строку.
2.  Склонируйте репозиторий:
    ```bash
    git clone <URL_ВАШЕГО_РЕПОЗИТОРИЯ> telegram-shop-hleb
    ```
    *Замените `<URL_ВАШЕГО_РЕПОЗИТОРИЯ>` на актуальный URL вашего Git-репозитория.*
3.  Перейдите в директорию проекта:
    ```bash
    cd telegram-shop-hleb
    ```

### Шаг 2: Настройка Переменных Окружения (`.env`)

Для работы приложения необходимо настроить переменные окружения.

*   **Файл `.env.example` и `.env`:**
    *   `.env.example` — это **шаблон** со списком необходимых переменных.
    *   Создайте файл `.env`, скопировав `.env.example`:
        ```bash
        cp .env.example .env
        ```
    *   Откройте `.env` и **обязательно** укажите ваш `TELEGRAM_BOT_TOKEN`:
        ```env
        TELEGRAM_BOT_TOKEN="ВАШ_УНИКАЛЬНЫЙ_ТОКЕН_БОТА"
        ```
        *(Получите токен у [@BotFather](https://t.me/BotFather) в Telegram.)*
    *   **Важно для `MINI_APP_BASE_URL`:** Эту переменную вам нужно будет установить **вручную** после запуска `localtunnel` (см. Шаг 4). Пример: `MINI_APP_BASE_URL="https://yoursubdomain.loca.lt"`
    *   Проверьте и при необходимости измените другие переменные в `.env` (порты, настройки БД и т.д.).

### Шаг 3: Запуск Docker Контейнеров
1.  Если это первый запуск или были изменения в Docker-конфигурации, соберите образы:
    ```bash
    docker-compose build
    ```
2.  Запустите все сервисы (веб-сервер, PHP, БД):
    ```bash
    docker-compose up -d
    ```
    *Примечание: Сервис `webhook-init` теперь не выполняет автоматическую настройку `localtunnel` и веб-хука. Он выведет информационное сообщение об этом.*

### Шаг 4: Настройка `localtunnel` и Веб-хука Telegram (Вручную)

Поскольку автоматическая настройка туннеля через `webhook-init` была отключена, эти шаги необходимо выполнить вручную:

1.  **Установите `localtunnel` глобально (если еще не установлен):**
    ```bash
    npm install -g localtunnel
    ```
2.  **Запустите `localtunnel`:**
    Откройте **новый терминал** и выполните команду, указав порт, на котором ваше приложение доступно локально через Docker (значение `SERVER_EXTERNAL_PORT` из вашего `.env` файла, по умолчанию `5125`):
    ```bash
    lt --port 5125
    ```
3.  **Получите публичный URL:** `localtunnel` выведет в консоль публичный HTTPS URL, например: `your url is: https://yoursubdomain.loca.lt`. **Скопируйте этот URL.** Этот URL будет активен, пока работает `localtunnel` в этом окне терминала.
4.  **Обновите `MINI_APP_BASE_URL` в `.env`:**
    *   Откройте файл `.env`.
    *   Вставьте скопированный URL в переменную `MINI_APP_BASE_URL`. Например:
        `MINI_APP_BASE_URL="https://yoursubdomain.loca.lt"`
    *   **Перезапустите PHP контейнер**, чтобы он подхватил новое значение (или все контейнеры):
        ```bash
        docker-compose restart php
        # или docker-compose down && docker-compose up -d
        ```
5.  **Установите Веб-хук для Telegram:**
    Вам нужно сообщить Telegram, на какой URL отправлять обновления для вашего бота. Используйте для этого метод `setWebhook` из Telegram Bot API. Это можно сделать с помощью `curl` в терминале или через любой другой HTTP-клиент.
    *   Замените `<ВАШ_TELEGRAM_BOT_TOKEN>` на ваш токен.
    *   Замените `<ВАШ_PUBLIC_LOCALTUNNEL_URL>` на URL, полученный от `localtunnel` на шаге 3.
    *   Замените `<ПУТЬ_К_ВЕБХУКУ>` на значение `TELEGRAM_WEBHOOK_PATH` из вашего `.env` (по умолчанию `/telegram_bot_webhook`).
    Пример команды `curl`:
    ```bash
    curl -F "url=<ВАШ_PUBLIC_LOCALTUNNEL_URL><ПУТЬ_К_ВЕБХУКУ>" "https://api.telegram.org/bot<ВАШ_TELEGRAM_BOT_TOKEN>/setWebhook"
    ```
    *Пример с данными:*
    ```bash
    curl -F "url=https://yoursubdomain.loca.lt/telegram_bot_webhook" "https://api.telegram.org/bot12345:ABCDEF/setWebhook"
    ```
    В ответ Telegram должен вернуть JSON с `"ok":true` и `"description":"Webhook was set"`.

### Шаг 5: Проверка Работоспособности
1.  Убедитесь, что `localtunnel` запущен в одном терминале, а Docker-контейнеры (`docker-compose up -d`) в другом.
2.  Отправьте команду `/start` вашему боту в Telegram.
3.  Бот должен ответить сообщением с кнопкой "🛍️ Открыть Магазин (Mini App)". Кнопка должна использовать URL, который вы указали в `MINI_APP_BASE_URL`.

### Шаг 6: Просмотр Логов
*   Логи PHP-приложения: `docker-compose logs php`
*   Логи Nginx: `docker-compose logs nginx`
*   Логи `webhook-init` (теперь будет содержать информационное сообщение о ручной настройке): `docker-compose logs webhook-init`

### Шаг 7: Остановка
*   Остановите `localtunnel` в его окне терминала (обычно `Ctrl+C`).
*   Остановите Docker-контейнеры: `docker-compose down`.

---

Это руководство описывает рекомендуемую структуру и начальную настройку для создания интернет-магазина в Telegram с использованием PHP, JS, фреймворка Hleb, Mini App и классических команд бота.

## Раздел 1: Общее Описание и Начало Работы (Сокращено)

### 1.1. Введение
Цель этого документа – предоставить разработчикам четкое понимание структуры проекта и основных шагов для начала работы над интернет-магазином в Telegram на базе Hleb. Мы будем создавать новые директории и файлы для лучшей организации кода.

### 1.4. Установка Зависимостей (PHP Telegram Bot SDK)
1.  Откройте `hleb/composer.json`.
2.  Добавьте SDK, например, `irazasyed/telegram-bot-sdk`:
    ```json
    "require": {
        "phphleb/framework": "^2.0",
        "irazasyed/telegram-bot-sdk": "^3.0" // Укажите актуальную версию
    }
    ```
3.  Выполните `docker-compose exec php composer update` (где `php` – имя вашего PHP сервиса в Docker).

---

## Раздел 2: Предлагаемая Структура Директорий и Файлов для Интернет-Магазина

Для организации кода интернет-магазина предлагается создать основную директорию `Shop` внутри `hleb/app/`.

### Директория: `hleb/app/Shop/`
**Назначение:** Эта директория будет корневой для всей бизнес-логики и компонентов, специфичных для интернет-магазина. Это помогает изолировать код магазина от остальной части приложения Hleb (если таковая имеется) и способствует лучшей организации.

#### Поддиректория: `hleb/app/Shop/Common/`
**Назначение:** Содержит общие классы, трейты, интерфейсы или базовые классы, которые могут использоваться несколькими модулями внутри `Shop` (например, `Product`, `Cart`, `Order`).

*   **Файл:** `hleb/app/Shop/Common/BaseShopController.php` (Опционально)
    **Назначение:** Может служить базовым контроллером для других контроллеров магазина. Здесь можно разместить общую логику, такую как проверка авторизации пользователя, инициализация общих для магазина сервисов или данных, необходимых для всех страниц магазина.

#### Поддиректория: `hleb/app/Shop/Product/`
**Назначение:** Модуль для управления каталогом товаров, отображения товаров, реализации фильтрации и поиска.

*   **Файл:** `hleb/app/Shop/Product/Controllers/ProductController.php`
    **Назначение:** Обрабатывает HTTP-запросы, связанные с продуктами. Отвечает за отображение списка товаров (каталога), детальной страницы товара, результатов поиска и фильтрации. Взаимодействует с `ProductService`, `ProductSearchService`, `ProductFilterService` и моделью `Product`. Также может обрабатывать Telegram команды, такие как `/catalog` и `/search`.

*   **Файл:** `hleb/app/Shop/Product/Models/Product.php`
    **Назначение:** Представляет модель данных для товара. Отвечает за взаимодействие с таблицей товаров в базе данных (получение, сохранение, обновление информации о товарах). Может содержать определения связей с другими моделями (например, категориями).

*   **Файл:** `hleb/app/Shop/Product/Services/ProductService.php`
    **Назначение:** Содержит бизнес-логику для управления товарами, не связанную напрямую с HTTP-запросами или базой данных. Например, расчет скидок, получение рекомендуемых товаров, формирование данных о товаре для отображения.

*   **Файл:** `hleb/app/Shop/Product/Services/ProductSearchService.php`
    **Назначение:** Реализует логику полнотекстового поиска товаров. Может взаимодействовать напрямую с базой данных (для простых LIKE запросов) или с внешним поисковым движком (Manticore, Elasticsearch).

*   **Файл:** `hleb/app/Shop/Product/Services/ProductFilterService.php`
    **Назначение:** Отвечает за применение фильтров к списку товаров по различным параметрам (цена, категория, характеристики и т.д.).

#### Поддиректория: `hleb/app/Shop/Cart/`
**Назначение:** Модуль для управления корзиной покупок пользователя.

*   **Файл:** `hleb/app/Shop/Cart/Controllers/CartController.php`
    **Назначение:** Обрабатывает запросы, связанные с корзиной: добавление товара в корзину, просмотр содержимого, изменение количества, удаление товара. Эти методы, вероятно, будут основными API-эндпоинтами для Mini App корзины, а также могут вызываться из Telegram команды `/cart`.

*   **Файл:** `hleb/app/Shop/Cart/Services/CartService.php`
    **Назначение:** Содержит основную бизнес-логику управления корзиной. Это может включать управление сессией корзины, расчет общей стоимости, применение купонов (если есть), взаимодействие с `ProductService` для получения информации о товарах в корзине.

#### Поддиректория: `hleb/app/Shop/Order/`
**Назначение:** Модуль для оформления и управления заказами.

*   **Файл:** `hleb/app/Shop/Order/Controllers/OrderController.php`
    **Назначение:** Обрабатывает запросы, связанные с процессом оформления заказа (checkout) и просмотром истории заказов (команда `/order_history`).

*   **Файл:** `hleb/app/Shop/Order/Models/Order.php`
    **Назначение:** Модель данных для заказа. Взаимодействует с таблицей заказов в БД.
*   **Файл:** `hleb/app/Shop/Order/Models/OrderItem.php`
    **Назначение:** Модель данных для позиций в заказе. Связана с моделью `Order`.

*   **Файл:** `hleb/app/Shop/Order/Services/OrderService.php`
    **Назначение:** Содержит бизнес-логику для создания заказа, обработки платежей (интеграция с платежными системами), управления статусами заказа, отправки уведомлений администратору и пользователю.

#### Поддиректория: `hleb/app/Shop/User/`
**Назначение:** Модуль для функционала, связанного с пользователем, например, "Избранное".

*   **Файл:** `hleb/app/Shop/User/Controllers/FavoriteController.php`
    **Назначение:** Обрабатывает запросы на добавление товара в избранное, удаление из избранного и просмотр списка избранных товаров.

*   **Файл:** `hleb/app/Shop/User/Services/FavoriteService.php`
    **Назначение:** Реализует бизнес-логику управления списком избранных товаров пользователя.

*   **Файл:** `hleb/app/Shop/User/Models/Favorite.php`
    **Назначение:** Модель данных для хранения избранных товаров пользователя, связывая пользователя и товар.

#### Поддиректория: `hleb/app/Shop/Admin/`
**Назначение:** Модуль для административной панели управления магазином.

*   **Файл:** `hleb/app/Shop/Admin/Controllers/AdminDashboardController.php`
    **Назначение:** Отображает главную страницу административной панели со статистикой и основными функциями.

*   **Файл:** `hleb/app/Shop/Admin/Controllers/AdminProductController.php`
    **Назначение:** Позволяет администраторам управлять товарами: добавлять новые, редактировать существующие, загружать фотографии, управлять остатками.

*   **Файл:** `hleb/app/Shop/Admin/Controllers/AdminCategoryController.php`
    **Назначение:** Управление категориями товаров.

*   **Файл:** `hleb/app/Shop/Admin/Controllers/AdminOrderController.php`
    **Назначение:** Просмотр и управление заказами, изменение их статусов.

    *(Для админ-панели также могут потребоваться свои Views, Requests для валидации форм и Services. Директория для Views может быть `hleb/app/Shop/Admin/Views/` или `hleb/resources/views/admin/` в зависимости от предпочтений и настроек Hleb.)*

#### Поддиректория: `hleb/app/Shop/Ai/`
**Назначение:** Модуль для интеграции с искусственным интеллектом (чат-бот).

*   **Файл:** `hleb/app/Shop/Ai/Controllers/AiChatController.php`
    **Назначение:** Принимает запросы от пользователей к чат-боту ИИ.

*   **Файл:** `hleb/app/Shop/Ai/Services/AiChatService.php`
    **Назначение:** Обрабатывает запрос пользователя, взаимодействует с моделью ИИ (внешней или локальной), использует базу знаний (статьи, информация о товарах) для генерации ответов.

### Директория: `hleb/public/shop_mini_app/`
**Назначение:** Содержит статические файлы (HTML, CSS, JavaScript, изображения) для вашего Mini App.
*   `index.html`: Главный файл Mini App.
*   `css/`: Папка для стилей.
*   `js/`: Папка для JavaScript кода Mini App.
*   `images/`: Папка для изображений.

### Директория: `hleb/config/`
*   **Файл:** `hleb/config/shop.php` (Новый файл)
    **Назначение:** Конфигурационный файл для настроек, специфичных для магазина (например, настройки платежных систем, опции доставки, ключи API сторонних сервисов, связанных с магазином, настройки ИИ).
*   **Файл:** `hleb/config/telegram.php` (Новый или модифицированный существующий)
    **Назначение:** Содержит конфигурацию для Telegram бота, такую как токен, путь вебхука (если не вынесен в `shop.php`). Загружает значения из `.env`.

---

## Раздел 3: Маршрутизация (`hleb/routes/map.php`)

Вам потребуется добавить множество маршрутов для всех новых контроллеров. Hleb позволяет гибко настраивать маршруты. Рекомендуется использовать группы маршрутов для лучшей организации.

Примеры (концептуально):
```php
// hleb/routes/map.php

use App\Shop\Product\Controllers\ProductController;
use App\Shop\Cart\Controllers\CartController;
use App\Shop\Order\Controllers\OrderController;
use App\Shop\User\Controllers\FavoriteController;
use App\Shop\Admin\Controllers\AdminDashboardController;
use App\Shop\Admin\Controllers\AdminProductController as AdminShopProductController; // Alias for admin product controller
// ... другие use для контроллеров

// Вебхук для Telegram бота
Route::post(config('telegram.webhook_path', '/telegram_bot_webhook'), [ProductController::class, 'handleWebhook']) // Или специальный BotController в Shop/Common/
    ->name('telegram.webhook');

// API для Mini App (пример)
Route::group('/api/miniapp/v1', function() {
    Route::get('/products', [ProductController::class, 'listForMiniApp'])->name('miniapp.products');
    Route::post('/cart/add', [CartController::class, 'addToCartMiniApp'])->name('miniapp.cart.add');
    // ... другие эндпоинты для Mini App
})->prefix('/api/miniapp/v1');


// Маршруты для каталога и товаров (публичная часть)
Route::get('/catalog', [ProductController::class, 'index'])->name('shop.catalog.index');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('shop.product.show');
Route::get('/search', [ProductController::class, 'search'])->name('shop.search');

// Маршруты для корзины (если доступна не только через Mini App)
Route::group('/cart', function() {
    Route::get('/', [CartController::class, 'view'])->name('shop.cart.view');
    Route::post('/add/{productId}', [CartController::class, 'add'])->name('shop.cart.add');
    // ... другие маршруты корзины
});

// Маршруты для заказов
Route::group('/order', function() {
    Route::get('/checkout', [OrderController::class, 'checkoutForm'])->name('shop.order.checkout');
    Route::post('/place', [OrderController::class, 'placeOrder'])->name('shop.order.place');
    Route::get('/history', [OrderController::class, 'history'])->name('shop.order.history')->middleware('UserAuthMiddleware'); // Пример middleware
});

// Маршруты для избранного
Route::group('/favorites', function() {
    Route::get('/', [FavoriteController::class, 'index'])->name('shop.favorites.index')->middleware('UserAuthMiddleware');
    Route::post('/add/{productId}', [FavoriteController::class, 'add'])->name('shop.favorites.add')->middleware('UserAuthMiddleware');
    // ...
})->middleware('UserAuthMiddleware');


// Маршруты для админ-панели
Route::group('/admin/shop', function() {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.shop.dashboard');
    // Пример ресурсного контроллера для продуктов в админке (если Hleb поддерживает такой синтаксис)
    // Route::resource('/products', AdminShopProductController::class);
    // Иначе, определять каждый маршрут (index, create, store, edit, update, destroy) отдельно
    Route::get('/products', [AdminShopProductController::class, 'index'])->name('admin.shop.products.index');
    Route::get('/products/create', [AdminShopProductController::class, 'create'])->name('admin.shop.products.create');
    Route::post('/products', [AdminShopProductController::class, 'store'])->name('admin.shop.products.store');
    Route::get('/products/{id}/edit', [AdminShopProductController::class, 'edit'])->name('admin.shop.products.edit');
    Route::put('/products/{id}', [AdminShopProductController::class, 'update'])->name('admin.shop.products.update');
    Route::delete('/products/{id}', [AdminShopProductController::class, 'destroy'])->name('admin.shop.products.destroy');
    // ... другие маршруты админки (категории, заказы)
})->prefix('admin/shop')->middleware('AdminAuthMiddleware'); // Пример middleware для аутентификации администратора

// Для `UserAuthMiddleware` и `AdminAuthMiddleware` потребуется создать соответствующие классы
// в `hleb/app/Middlewares/` или, например, `hleb/app/Shop/Common/Middlewares/`.
```

---

## Раздел 4: Mini App

*   **Статические файлы:** Размещаются в новой директории `hleb/public/shop_mini_app/`. Это включает `index.html`, файлы CSS, JavaScript и изображения.
*   **Взаимодействие с бэкендом:** Mini App будет отправлять AJAX-запросы (например, используя `fetch` или `axios` в JavaScript) на API-маршруты, определенные в `hleb/routes/map.php` (например, на эндпоинты в группе `/api/miniapp/v1`). Эти методы контроллеров (например, в `CartController`, `ProductController`) будут обрабатывать запросы и возвращать данные в формате JSON.
*   **Запуск Mini App:** Бот будет отправлять пользователю кнопку или ссылку. URL для Mini App будет формироваться из `MINI_APP_BASE_URL` (из `.env`, который указывает на ваш `localtunnel` URL) и пути к `index.html` вашего Mini App, например: `https://yoursubdomain.loca.lt/shop_mini_app/` (если веб-сервер настроен на автоматический поиск `index.html` в директории) или `https://yoursubdomain.loca.lt/shop_mini_app/index.html`.

---

## Раздел 5: Дальнейшие Шаги Разработки

1.  **Настройте базу данных:** Создайте схему БД для товаров, категорий, заказов, пользователей, избранного и т.д. Если Hleb поддерживает систему миграций, используйте её. В противном случае, подготовьте SQL-скрипты для создания таблиц.
2.  **Реализуйте модели:** Создайте PHP классы для моделей данных (например, `Product.php`, `Order.php` в соответствующих поддиректориях `hleb/app/Shop/.../Models/`).
3.  **Начните с модуля `Product`:** Реализуйте CRUD операции для товаров в админ-панели и отображение каталога/товаров для пользователей.
4.  **Реализуйте модуль `Cart`:** Логика добавления в корзину, просмотра и изменения содержимого.
5.  **Разработайте Mini App UI/UX:** Параллельно с бэкендом для каталога и корзины, создавайте интерфейс Mini App.
6.  **Последовательно реализуйте** остальные модули: `Order` (оформление заказа), `User` (Избранное, история заказов), `Admin` (управление заказами, категориями), `Ai` (чат-бот).
7.  **Настройте Telegram команды** в соответствующем контроллере (`ProductController` или выделенный `BotController`), который будет обрабатывать вебхуки от Telegram. Свяжите команды (`/start`, `/catalog`, `/cart` и т.д.) с методами ваших сервисов и контроллеров.

Это руководство предоставляет основу для структурирования вашего проекта интернет-магазина. Детальная реализация каждого компонента потребует дополнительной проработки. Удачи в разработке!
---

## Раздел 6: Общая Структура Проекта (Пример)

Ниже представлена примерная общая структура директорий проекта, включая существующие элементы и предлагаемые новые компоненты для интернет-магазина. Это поможет вам визуализировать, как модули магазина будут интегрированы в проект Hleb.

```text
.
├── .env
├── .gitignore
├── LICENSE
├── README.md (этот файл)
├── docker-compose.yml
├── docker/
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── root/
│   │   ├── .bash_profile
│   │   ├── .bashrc
│   │   └── ... (другие скрипты)
│   ├── src/
│   │   └── ... (конфигурационные PHP файлы для Docker)
│   └── xdebug.ini
└── hleb/
    ├── .gitattributes
    ├── .gitignore
    ├── .php-cs-fixer.php
    ├── app/
    │   ├── Bootstrap/
    │   │   └── ... (существующие файлы Bootstrap)
    │   ├── Commands/
    │   │   ├── DefaultTask.php
    │   │   └── RotateLogs.php
    │   ├── Controllers/
    │   │   └── DefaultController.php
    │   ├── Middlewares/
    │   │   └── DefaultMiddleware.php
    │   ├── Models/
    │   │   └── DefaultModel.php
    │   └── Shop/  # <-- Новая директория для модуля магазина
    │       ├── Common/
    │       │   └── BaseShopController.php
    │       ├── Product/
    │       │   ├── Controllers/
    │       │   │   └── ProductController.php
    │       │   ├── Models/
    │       │   │   └── Product.php
    │       │   └── Services/
    │       │       ├── ProductService.php
    │       │       ├── ProductSearchService.php
    │       │       └── ProductFilterService.php
    │       ├── Cart/
    │       │   ├── Controllers/
    │       │   │   └── CartController.php
    │       │   └── Services/
    │       │       └── CartService.php
    │       ├── Order/
    │       │   ├── Controllers/
    │       │   │   └── OrderController.php
    │       │   ├── Models/
    │       │   │   ├── Order.php
    │       │   │   └── OrderItem.php
    │       │   └── Services/
    │       │       └── OrderService.php
    │       ├── User/ # Для "Избранного" и других пользовательских функций
    │       │   ├── Controllers/
    │       │   │   └── FavoriteController.php
    │       │   ├── Models/
    │       │   │   └── Favorite.php
    │       │   └── Services/
    │       │       └── FavoriteService.php
    │       ├── Admin/ # Модуль административной панели
    │       │   ├── Controllers/
    │       │   │   ├── AdminDashboardController.php
    │       │   │   ├── AdminProductController.php
    │       │   │   ├── AdminCategoryController.php
    │       │   │   └── AdminOrderController.php
    │       │   └── Views/  # (или hleb/resources/views/admin/)
    │       │       └── ... (шаблоны для админ-панели)
    │       └── Ai/ # Модуль для интеграции с ИИ
    │           ├── Controllers/
    │           │   └── AiChatController.php
    │           └── Services/
    │               └── AiChatService.php
    ├── config/
    │   ├── common.php
    │   ├── database.php
    │   ├── main.php
    │   ├── system.php
    │   ├── shop.php        # <-- Новый конфигурационный файл для магазина
    │   └── telegram.php    # <-- Новый/обновленный конфигурационный файл для Telegram
    ├── console   # Исполняемый файл консоли Hleb
    ├── public/   # Публичная директория, доступная через веб-сервер
    │   ├── .htaccess
    │   ├── css/
    │   ├── images/
    │   ├── js/
    │   ├── favicon.ico
    │   ├── index.php       # Главный входной файл Hleb
    │   ├── robots.txt
    │   └── shop_mini_app/  # <-- Новая директория для статических файлов Mini App
    │       ├── index.html
    │       ├── css/
    │       │   └── main.css
    │       ├── js/
    │       │   └── app.js
    │       └── images/
    ├── readme.md (файл readme.md фреймворка Hleb, не основной README проекта)
    ├── resources/
    │   └── views/          # Директория для шаблонов Hleb (если используются)
    │       ├── default.php
    │       ├── error.php
    │       └── admin/      # (альтернативное место для шаблонов админ-панели, если не SPA)
    │           └── ...
    ├── routes/
    │   └── map.php         # Файл определения маршрутов (будет значительно изменен)
    └── vendor/
        └── ... (директория зависимостей Composer)

```
**Примечание:** Некоторые стандартные файлы и директории Hleb (например, содержимое `Bootstrap`, некоторые файлы в `public/css`, `js`, `images`) показаны сокращенно (`...`) для наглядности основной структуры. Ключевые новые дополнения для интернет-магазина отмечены комментариями `# <-- ...`.
---
