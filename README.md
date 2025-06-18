# Toaster

[![HLEB2](https://img.shields.io/badge/HLEB-2-darkcyan)](https://github.com/phphleb/hleb) [![License: MIT](https://img.shields.io/badge/License-MIT%20(Free)-brightgreen.svg)](https://github.com/phphleb/hleb/blob/master/LICENSE)

A template for local development using the [HLEB2](https://github.com/phphleb/hleb) framework.

## Recipe

- [Docker](https://www.docker.com)
- this repository
- `docker-compose up -d`

## Composition

<details>
  <summary>Development repository</summary>

  After launching the containers, the `hleb` directory will be created in the root of the project
  with the new [HLEB2](https://packagist.org/packages/phphleb/hleb) project.
</details>

<details>
  <summary>Local server</summary>

  Default [localhost:5125](http://localhost:5125).
  If you are not satisfied with the port, change `SERVER_EXTERNAL_PORT` in the `.env` file.
</details>

<details>
  <summary>MariaDB</summary>

  [About MariaDB](https://mariadb.org/)  
  In the new project `hleb` the file will be automatically created
  `/config/database-local.php` with the configuration for connecting to the DBMS.
</details>

<details>
  <summary>phpMyAdmin</summary>

  [About phpMyAdmin](https://www.phpmyadmin.net/)  
  Default [localhost:8080](http://localhost:8080).
  Authorization is automatic.
  If you are not satisfied with the port, change `PMA_EXTERNAL_PORT` in the `.env` file.
</details>

<details>
  <summary>Xdebug</summary>

  [About Xdebug](https://xdebug.org/)  
  The configuration file is `docker/xdebug.ini`.
  The default port is `9003`.
  In `docker-compose.yml` the server is specified as `serverName`.
  Defaults to `serverName=toaster`.
</details>

<details>
  <summary>PHP Coding Standards Fixer</summary>

  [About PHP CS Fixer](https://cs.symfony.com/)  
  The [configuration](https://cs.symfony.com/doc/config.html) from `docker/.php-cs-fixer.php` is copied to `/hleb`.
  Cheat sheet on the rules [here](https://mlocati.github.io/php-cs-fixer-configurator/#version:3.7).
  After creating a new project, it automatically edits files using rules.
</details>

## HLOGIN
User authorization module.

[About HLOGIN](https://github.com/phphleb/hlogin)

Not installed by default, but can be easily added to your project.
Connect to the `php` service container and execute `./add-hlogin.sh`.

During installation, you will need to answer three questions from the system:

1. Preferred interface style.
2. Administrator's email.
3. Administrator password.

## Telegram Bot and Mini App Development

This guide walks you through setting up and developing a Telegram bot with a Mini App (Web App) within this Hleb project.

**Step 1: Ensure Your Hleb Environment is Running**

Before you begin bot development, make sure your basic Hleb project environment is up and running using Docker.
- Follow the instructions in the "## Recipe" section (e.g., run `docker-compose up -d`).
- Confirm you can access your Hleb application locally (usually at `http://localhost:5125`, as noted in "## Composition" > "Local server").

**Step 2: Configure Environment Variables for the Bot**

You'll need to add credentials for your Telegram bot to the `.env` file at the root of the project. If `.env` doesn't exist, you might need to copy it from a sample like `.env.example` if provided (though this project structure doesn't explicitly show one, ensure your `.env` is correctly set up for Hleb itself).

Add the following lines to your `.env` file, replacing placeholders with your actual values:

```env
TELEGRAM_BOT_TOKEN=your_actual_telegram_bot_token
TELEGRAM_WEBHOOK_URL=your_public_webhook_url_from_localtunnel_in_step_6
MINI_APP_URL=your_public_mini_app_url_from_localtunnel_in_step_6
# You can also add your bot's username if needed for logic
# TELEGRAM_BOT_USERNAME=your_bot_username
```

**Step 3: Add a PHP Telegram Bot SDK**

To interact with the Telegram Bot API, you'll need a PHP library. A common choice is `irazasyed/telegram-bot-sdk` or `php-telegram-bot/core`.

1.  Open your `hleb/composer.json` file.
2.  Add the chosen SDK to the `require` section. For example, for `irazasyed/telegram-bot-sdk`:
    ```json
    "require": {
        "phphleb/framework": "^2.0",
        "irazasyed/telegram-bot-sdk": "^3.0" // Check for the latest stable version
        // ... other dependencies
    }
    ```
3.  Install the SDK by running Composer from within your Docker PHP container or your local environment if you have PHP/Composer set up and mapped to the `hleb` directory:
    ```bash
    # If accessing via Docker (assuming your php service is named 'php'):
    docker-compose exec php composer update
    # Or, if you have PHP/Composer locally and hleb/ is your working directory:
    # composer update
    ```

**Step 4: Create Bot and Mini App File Structure**

All new files and directories for the bot and Mini App will be created inside the `hleb` directory, adhering to Hleb's conventions.

1.  **Bot Controllers** (for handling incoming requests):
    - Create directory: `hleb/app/Controllers/Bot/`
    - Create file: `hleb/app/Controllers/Bot/TelegramWebhookController.php` (handles webhook requests from Telegram)
    - Create file (optional): `hleb/app/Controllers/Bot/MiniAppController.php` (if your Mini App needs dedicated backend endpoints)

2.  **Bot Services** (for business logic):
    - Create directory: `hleb/app/Services/` (if it doesn't already exist)
    - Create directory: `hleb/app/Services/Bot/`
    - Create file: `hleb/app/Services/Bot/TelegramBotService.php` (core bot logic, interaction with Telegram API)
    - Create file: `hleb/app/Services/Bot/MiniAppService.php` (backend logic for your Mini App)

3.  **Bot Configuration**:
    - Create file: `hleb/config/telegram.php`
      This file will load your bot's settings (like the token from `.env`). Example content:
      ```php
      <?php // hleb/config/telegram.php
      return [
          'bot_token' => env('TELEGRAM_BOT_TOKEN'),
          'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
          'mini_app_base_url' => env('MINI_APP_URL'),
          // 'bot_username' => env('TELEGRAM_BOT_USERNAME'),
      ];
      ```

4.  **Mini App Frontend Files**:
    - Create directory: `hleb/public/mini_app/`
    - Create file: `hleb/public/mini_app/index.html` (main page for your Mini App)
    - Create directory: `hleb/public/mini_app/css/`
    - Create file: `hleb/public/mini_app/css/style.css`
    - Create directory: `hleb/public/mini_app/js/`
    - Create file: `hleb/public/mini_app/js/script.js`
    - Create directory (optional): `hleb/public/mini_app/assets/` (for images, fonts, etc.)

5.  **Bot Console Commands** (Optional but Recommended):
    - Create directory: `hleb/app/Commands/Bot/`
    - Create file: `hleb/app/Commands/Bot/SetWebhookCommand.php` (to help set your bot's webhook URL with Telegram)
    - Create file: `hleb/app/Commands/Bot/BotInfoCommand.php` (to test bot token or get info)

**Step 5: Define Bot Routes**

You need to tell Hleb how to direct incoming web requests to your bot controller.

1.  Open `hleb/routes/map.php`.
2.  Add a route for your Telegram webhook. This URL must match what you set in `TELEGRAM_WEBHOOK_URL` (without the domain part, as Hleb handles that). For example:
    ```php
    // Existing routes
    // Route::get('/', view('default'))->name('homepage');

    // Add this for the Telegram Bot Webhook
    Route::post('/telegram/webhook', [App\Controllers\Bot\TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

    // Optional: Add any routes needed for your Mini App's backend API
    // Route::post('/api/mini_app/data', [App\Controllers\Bot\MiniAppController::class, 'saveData'])->name('mini_app.save.data');
    ```

**Step 6: Make Your Local Server Accessible (Tunneling)**

Telegram needs to send updates to a public URL. When developing locally, your server (`localhost:5125`) is not public. A tunneling service like `localtunnel` can create a temporary public URL for you.

1.  **Install `localtunnel`** (if you haven't already):
    Requires Node.js & npm. Run on your host machine (not inside Docker):
    ```bash
    npm install -g localtunnel
    ```
2.  **Run `localtunnel`**:
    - Ensure your Hleb project (Docker) is running and accessible on `localhost:5125` (or the port set by `SERVER_EXTERNAL_PORT` in `.env`).
    - Open a new terminal on your host machine and run:
      ```bash
      lt --port 5125
      # Or use the port from your .env: lt --port ${SERVER_EXTERNAL_PORT:-5125}
      ```
3.  **Get Your Public URL**:
    `localtunnel` will output a URL like `https://yoursubdomain.loca.lt`. This is your temporary public base URL.
4.  **Update `.env`**:
    - Set `TELEGRAM_WEBHOOK_URL` to this public URL + your webhook path: `TELEGRAM_WEBHOOK_URL=https://yoursubdomain.loca.lt/telegram/webhook`
    - Set `MINI_APP_URL` to this public URL + your Mini App path: `MINI_APP_URL=https://yoursubdomain.loca.lt/mini_app/`
    *(You might need to restart your Hleb application or ensure it re-reads the .env if you changed these while it was running, though typically config is cached).*
5.  **Set Your Webhook with Telegram**:
    - You need to tell Telegram to send bot updates to your `TELEGRAM_WEBHOOK_URL`. You can do this:
        - Programmatically using the `SetWebhookCommand.php` you created (you'll need to implement its logic using the Bot SDK).
        - Manually via a curl request or a simple PHP script using the Bot SDK's `setWebhook` method.
        - Example using curl (replace with your actual token and URL):
          ```bash
          curl -F "url=https://yoursubdomain.loca.lt/telegram/webhook" https://api.telegram.org/botYOUR_TELEGRAM_BOT_TOKEN/setWebhook
          ```

**Important Tunneling Notes:**
  - **Accessibility:** Verify `localtunnel` and `loca.lt` are accessible in Russia. If not, explore alternatives like `Serveo.net`, `Tunnelmole`, or `localhost.run`.
  - **Dynamic URL:** `localtunnel` usually gives a new URL each time. For a fixed URL, explore paid options or other services.
  - **HTTPS:** Telegram requires HTTPS for webhooks, which `localtunnel` provides.

**Step 7: Start Developing Your Bot and Mini App**

-   **Bot Logic**:
    -   Start by implementing the `handle()` method in `hleb/app/Controllers/Bot/TelegramWebhookController.php`. This method will receive updates from Telegram.
    -   Pass the update data to `hleb/app/Services/Bot/TelegramBotService.php` to process commands, messages, etc.
    -   Use the Telegram Bot SDK (configured in your service, likely using the token from `config('telegram.bot_token')`) to send messages, keyboards, etc.
-   **Mini App Frontend**:
    -   Develop your Mini App's UI and logic in `hleb/public/mini_app/index.html`, `script.js`, and `style.css`.
    -   The Mini App will be launched via a button or link sent by your bot, using the `MINI_APP_URL`.
-   **Mini App Backend (Optional)**:
    -   If your Mini App needs to save data or perform actions on the server, implement these in `hleb/app/Controllers/Bot/MiniAppController.php` and/or `hleb/app/Services/Bot/MiniAppService.php`. Define routes for these actions in `hleb/routes/map.php`.

This structured approach should help you get your Telegram bot and Mini App running within the Hleb framework. Remember to consult the Hleb documentation and the documentation for your chosen Telegram Bot SDK for more detailed information.
