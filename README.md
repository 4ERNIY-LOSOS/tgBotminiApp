## Раздел 0: Быстрая Установка и Запуск
Этот раздел поможет вам быстро настроить и запустить проект "Интернет-Магазин в Telegram на Hleb".

### Предварительные Требования
*   Установленный Git.
*   Установленные Docker и Docker Compose (версия Docker Compose V2 или новее).

### Шаг 1: Клонирование Репозитория
1.  Откройте терминал или командную строку.
2.  Выполните следующие команды для клонирования репозитория и перехода в директорию проекта:
    ```bash
    git clone <URL_РЕПОЗИТОРИЯ_ВАШЕГО_ПРОЕКТА> shop-telegram-hleb
    cd shop-telegram-hleb
    ```
    *Замените `<URL_РЕПОЗИТОРИЯ_ВАШЕГО_ПРОЕКТА>` на актуальный URL вашего Git-репозитория.*

### Шаг 2: Настройка Переменных Окружения (`.env`)

#### Что такое `.env.example` и `.env`?
*   **`.env.example`**: Это файл-шаблон, который демонстрирует, какие переменные окружения необходимы для работы приложения. Он содержит список всех настроек с примерами значений или пустыми строками. Этот файл можно безопасно коммитить в Git.
*   **`.env`**: Это файл, который вы создаете, копируя содержимое `.env.example`. В этом файле вы указываете ваши **реальные, персональные** значения для настроек, включая чувствительные данные, такие как токен вашего Telegram бота. **Файл `.env` никогда не должен добавляться в систему контроля версий (например, Git)**, так как он содержит конфиденциальную информацию. В данном проекте он уже добавлен в `.gitignore`, чтобы предотвратить случайное добавление.

#### Создание вашего файла `.env`:
В корневой директории проекта выполните команду:
```bash
cp .env.example .env
```
Эта команда скопирует шаблон в новый файл `.env`.

#### Обязательные настройки в `.env`:
1.  Откройте только что созданный файл `.env` в вашем любимом текстовом редакторе.
2.  Найдите строку `TELEGRAM_BOT_TOKEN=""`.
3.  Вставьте ваш уникальный токен Telegram бота, который вы получили от [@BotFather](https://t.me/BotFather), между кавычками.
    *   Пример: `TELEGRAM_BOT_TOKEN="1234567890:ABCDEFGHIJKLMNopqrstuvwxyz12345"`
4.  (Опционально) Просмотрите другие настройки в файле `.env`, такие как `SERVER_EXTERNAL_PORT` (порт, на котором будет доступен ваш веб-сервер локально), `DATABASE_EXTERNAL_PORT` (порт для подключения к БД извне контейнера), `MYSQL_USER`, `MYSQL_PASSWORD`. Измените их, если это необходимо для вашего локального окружения. Однако стандартные значения обычно подходят для первого запуска.

#### Замечание по `MINI_APP_BASE_URL`:
При использовании Docker для запуска проекта (как описано в следующих шагах), URL-адрес для вашего Mini App (который используется для формирования кнопки "Открыть Магазин" в Telegram боте) будет **автоматически определен и использован приложением**. Это происходит благодаря сервису `webhook-init`, который запускает `localtunnel` и передает полученный публичный URL в PHP-приложение.

Таким образом:
*   Вам обычно **не нужно** вручную устанавливать `MINI_APP_BASE_URL` в файле `.env`, если вы работаете в Docker-окружении.
*   Значение `MINI_APP_BASE_URL` в `.env` может служить:
    1.  Резервным вариантом (фолбэком), если автоматическое определение по какой-либо причине не сработало.
    2.  Для локальной разработки **без Docker**, когда вы запускаете `localtunnel` (или аналогичный сервис) вручную и хотите указать URL напрямую для PHP-приложения.

### Шаг 3: Сборка и Запуск Docker Контейнеров

1.  Если это ваш первый запуск проекта, или если вы вносили изменения в файлы конфигурации Docker (например, `Dockerfile` или скрипты в директории `docker/`), рекомендуется сначала собрать образы Docker:
    ```bash
    docker-compose build
    ```
2.  После завершения сборки (или если образы уже собраны), запустите все сервисы проекта в фоновом режиме (detached mode):
    ```bash
    docker-compose up -d
    ```
    Эта команда запустит веб-сервер Nginx, PHP-FPM приложение, базу данных MariaDB и вспомогательный сервис `webhook-init`.

### Шаг 4: Автоматическая Настройка (Что происходит "под капотом")

После успешного выполнения `docker-compose up -d`, сервис `webhook-init` автоматически выполнит следующие важные действия:
1.  **Запуск `localtunnel`**: `webhook-init` запустит `localtunnel` (или аналогичный инструмент, если настроен) для создания публичного HTTPS URL. Этот URL будет туннелировать запросы из интернета на ваш локальный веб-сервер (Nginx), работающий внутри Docker-контейнера.
2.  **Регистрация Веб-хука Telegram**: Используя ваш `TELEGRAM_BOT_TOKEN` (из файла `.env`), сервис отправит запрос к Telegram API, чтобы зарегистрировать полученный публичный URL в качестве веб-хука для вашего бота. Это необходимо, чтобы Telegram мог отправлять обновления (например, сообщения и команды от пользователей) на ваше работающее приложение.
3.  **Передача URL в PHP-приложение**: `webhook-init` сохранит этот публичный URL в специальный файл (`/run/shared_config/localtunnel_url.txt`) внутри общего Docker-volume. PHP-приложение (в частности, контроллер `ProductController`) настроено так, чтобы при обработке команды `/start` считывать этот URL и использовать его для динамического формирования правильной ссылки на Mini App в кнопке ответа.

### Шаг 5: Проверка Работоспособности

1.  Откройте приложение Telegram.
2.  Найдите вашего бота (по имени пользователя, которое вы задали при его создании через BotFather).
3.  Отправьте боту команду `/start`.
4.  В ответ вы должны получить приветственное сообщение и кнопку с текстом вроде "🛍️ Открыть Магазин (Mini App)".
5.  Нажмите на эту кнопку. Ваш Mini App должен открыться в интерфейсе Telegram, загрузив `index.html` из директории `hleb/public/shop_mini_app/`.

### Шаг 6: Просмотр Логов и Отладка
Если что-то пошло не так, или вы просто хотите увидеть детали работы различных компонентов системы, используйте следующие команды для просмотра логов Docker-контейнеров:

*   **Логи сервиса инициализации веб-хука (`webhook-init`):**
    Особенно полезны для того, чтобы увидеть, какой публичный URL был получен от `localtunnel`, и успешно ли был установлен веб-хук для Telegram.
    ```bash
    docker-compose logs webhook-init
    ```
*   **Логи PHP-приложения (`php` сервис):**
    Здесь будут отображаться ошибки выполнения PHP-скриптов, сообщения, которые вы выводите с помощью `Log::info()`, `Log::error()` и т.д. в вашем Hleb-приложении.
    ```bash
    docker-compose logs php
    ```
*   **Логи веб-сервера Nginx (`nginx` сервис):**
    Содержат информацию о всех входящих HTTP-запросах, ошибках сервера Nginx и т.д.
    ```bash
    docker-compose logs nginx
    ```

### Шаг 7: Остановка Окружения

1.  Чтобы остановить все запущенные Docker-контейнеры, связанные с проектом:
    ```bash
    docker-compose down
    ```
    Эта команда остановит и удалит контейнеры. Данные вашей базы данных (MariaDB) должны сохраниться, так как для нее настроен Docker-volume, который сохраняет данные между перезапусками контейнеров.
2.  При следующем запуске проекта с помощью `docker-compose up -d`, сервис `webhook-init` снова попытается получить новый (обычно другой) URL от `localtunnel` и автоматически обновит веб-хук для вашего Telegram бота.

---

# Руководство по Разработке Интернет-Магазина в Telegram на Hleb

Это руководство описывает рекомендуемую структуру и начальную настройку для создания интернет-магазина в Telegram с использованием PHP, JS, фреймворка Hleb, Mini App и классических команд бота.

## Раздел 1: Общее Описание и Начало Работы

### 1.1. Введение
Цель этого документа – предоставить разработчикам четкое понимание структуры проекта и основных шагов для начала работы над интернет-магазином в Telegram на базе Hleb. Мы будем создавать новые директории и файлы для лучшей организации кода.

### 1.2. Настройка Docker и Запуск Проекта Hleb
Убедитесь, что ваш основной проект Hleb корректно настроен и запущен с использованием Docker.
1.  **Запустите Docker контейнеры:**
    В корневой директории вашего проекта выполните:
    ```bash
    docker-compose up -d
    ```
2.  **Проверьте доступность проекта:**
    Проект должен быть доступен по адресу `http://localhost:5125` (или порту из `SERVER_EXTERNAL_PORT` в `.env`).

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

### 1.6. Пример Взаимодействия с Ботом

После успешной настройки и запуска:
*   Отправьте команду `/start` вашему боту в Telegram.
*   В ответ вы должны получить приветственное сообщение, а также **кнопку "🛍️ Открыть Магазин (Mini App)"**, которая откроет ваше веб-приложение Mini App прямо в интерфейсе Telegram.

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
