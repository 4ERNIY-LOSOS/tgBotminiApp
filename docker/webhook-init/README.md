# Webhook Initialization Service (`docker/webhook-init/`)

This directory contains the configuration and entrypoint script for the `webhook-init` Docker service. The primary purpose of this service is to automate the setup of the Telegram bot's webhook during the deployment or startup phase of the application.

## Functionality:

When the `webhook-init` service starts (as defined in `docker-compose.yml`), it executes the `entrypoint-webhook.sh` script. This script performs the following actions:

1.  **Reads Environment Variables:** It retrieves necessary configuration from environment variables, which are typically passed from the `.env` file via `docker-compose.yml`. These include:
    *   `TELEGRAM_BOT_TOKEN`: The authentication token for your Telegram bot.
    *   `TELEGRAM_WEBHOOK_PATH`: The specific path on your server where Telegram should send webhook updates (e.g., `/telegram_bot_webhook`).
    *   `TARGET_SERVICE_HOST`: The hostname of the service within the Docker network that localtunnel should target (usually the Nginx or PHP application service, e.g., `nginx`).
    *   `TARGET_SERVICE_PORT`: The internal port of the target service (e.g., `80`).
    *   Optional variables for `localtunnel` like `LT_SUBDOMAIN`, `MAX_LT_RETRIES`, `LT_START_TIMEOUT`.

2.  **Starts Localtunnel:** It launches `localtunnel` to expose the local `TARGET_SERVICE_HOST` (e.g., your Nginx container) to the public internet with a temporary HTTPS URL. This is crucial for local development when your application isn't directly accessible from the internet.

3.  **Retrieves Public URL:** The script waits for `localtunnel` to provide a public URL (e.g., `https://yoursubdomain.loca.lt`).

4.  **Sets Telegram Webhook:** Once the public URL is obtained, the script constructs the full webhook URL by appending the `TELEGRAM_WEBHOOK_PATH` to the `localtunnel` URL. It then makes an API call to the Telegram Bot API (`setWebhook` method) to register this full URL as the endpoint for your bot.

5.  **Maintains Tunnel:** After successfully setting the webhook, the service keeps `localtunnel` running to maintain the public URL and ensure Telegram can continue to send updates. If `localtunnel` stops, the service (and container) will likely exit.

## Docker Configuration:

*   **`Dockerfile`**: Defines the Docker image for this service. It typically installs Node.js (for `localtunnel`), `curl` (for making the API call to Telegram), and copies the `entrypoint-webhook.sh` script.
*   **`entrypoint-webhook.sh`**: The shell script that orchestrates the steps described above.

## Importance:

This service automates a critical setup step for local development and potentially for some deployment scenarios:
*   **Local Development:** Allows developers to receive Telegram webhook updates on their local machine without manual `localtunnel` setup and webhook registration.
*   **Dynamic IPs/Ports:** If the public-facing URL changes frequently, this service can help automate the webhook update process.

**Note:** For production environments, a stable public URL is generally preferred, and webhook setup might be a one-time manual process or handled by deployment scripts rather than `localtunnel`. However, `localtunnel` is very convenient for development and testing.
