#!/bin/bash
# Script to be run as the entrypoint for the webhook-init Docker service.

echo "Webhook Initializer: Starting up..."

# --- Configuration from Environment Variables ---
# Required:
# TELEGRAM_BOT_TOKEN: Your Telegram Bot Token.
# TELEGRAM_WEBHOOK_PATH: The path for your webhook (e.g., /telegram_bot_webhook).
# TARGET_SERVICE_HOST: The hostname/IP of the service localtunnel should target (e.g., 'nginx', 'php_app_service_name').
# TARGET_SERVICE_PORT: The port of the target service (e.g., '80' for nginx).

# Optional:
# LT_SUBDOMAIN: Preferred localtunnel subdomain (availability not guaranteed).
# MAX_LT_RETRIES: How many times to retry starting localtunnel if it fails (default 3).
# LT_START_TIMEOUT: Seconds to wait for localtunnel to provide a URL (default 20).

# Validate required environment variables
if [ -z "\$TELEGRAM_BOT_TOKEN" ]; then
    echo "Webhook Initializer: Error - TELEGRAM_BOT_TOKEN is not set. Exiting."
    exit 1
fi
if [ -z "\$TELEGRAM_WEBHOOK_PATH" ]; then
    echo "Webhook Initializer: Error - TELEGRAM_WEBHOOK_PATH is not set. Exiting."
    exit 1
fi
if [ -z "\$TARGET_SERVICE_HOST" ]; then
    echo "Webhook Initializer: Error - TARGET_SERVICE_HOST is not set. Exiting."
    exit 1
fi
if [ -z "\$TARGET_SERVICE_PORT" ]; then
    echo "Webhook Initializer: Error - TARGET_SERVICE_PORT is not set. Exiting."
    exit 1
fi

MAX_RETRIES=\${MAX_LT_RETRIES:-3}
LT_TIMEOUT=\${LT_START_TIMEOUT:-20} # seconds to wait for URL
LT_ATTEMPT=0
PUBLIC_URL=""

while [ -z "\$PUBLIC_URL" ] && [ \$LT_ATTEMPT -lt \$MAX_RETRIES ]; do
    LT_ATTEMPT=\$(expr \$LT_ATTEMPT + 1)
    echo "Webhook Initializer: Attempt \$LT_ATTEMPT of \$MAX_RETRIES to start localtunnel for http://\$TARGET_SERVICE_HOST:\$TARGET_SERVICE_PORT..."

    # Temporary file to capture localtunnel output
    LT_OUTPUT_FILE="/tmp/lt_output.log"
    rm -f "\$LT_OUTPUT_FILE" # Clean up from previous attempts

    # Start localtunnel.
    # Using --host to specify the target host within the Docker network.
    # --print-requests false to minimize noise.
    # The --local-host parameter might be needed if TARGET_SERVICE_HOST is not directly routable by lt's proxy logic
    # For now, assuming TARGET_SERVICE_HOST is resolvable or is an IP.
    lt --port "\$TARGET_SERVICE_PORT" --host "\$TARGET_SERVICE_HOST"        \${LT_SUBDOMAIN:+--subdomain "\$LT_SUBDOMAIN"}        --print-requests false > "\$LT_OUTPUT_FILE" 2>&1 &
    LT_PID=\$!

    echo "Webhook Initializer: Waiting up to \$LT_TIMEOUT seconds for localtunnel URL..."

    for i in \$(seq 1 \$LT_TIMEOUT); do
        PUBLIC_URL=\$(grep -o 'https://[a-zA-Z0-9.-]*\(loca\.lt\|tunnelto\.dev\)' "\$LT_OUTPUT_FILE")
        if [ ! -z "\$PUBLIC_URL" ]; then
            break
        fi
        sleep 1
    done

    if [ -z "\$PUBLIC_URL" ]; then
        echo "Webhook Initializer: Could not get localtunnel URL on attempt \$LT_ATTEMPT."
        echo "--- Localtunnel Output (lt_output.log) ---"
        cat "\$LT_OUTPUT_FILE"
        echo "--- End Localtunnel Output ---"
        kill \$LT_PID
        wait \$LT_PID 2>/dev/null
        if [ \$LT_ATTEMPT -lt \$MAX_RETRIES ]; then
            echo "Webhook Initializer: Retrying in 5 seconds..."
            sleep 5
        fi
    else
        PUBLIC_URL=\$(echo "\$PUBLIC_URL" | head -n 1)
        echo "Webhook Initializer: Successfully obtained Localtunnel URL: \$PUBLIC_URL"
    fi
done

if [ -z "\$PUBLIC_URL" ]; then
    echo "Webhook Initializer: Failed to obtain localtunnel URL after \$MAX_RETRIES attempts. Exiting."
    rm -f "\$LT_OUTPUT_FILE"
    exit 1
fi

FORMATTED_WEBHOOK_PATH=\$(echo "\$TELEGRAM_WEBHOOK_PATH" | sed 's:^/*::; s:/*\$::; s:^:\/:')
FULL_WEBHOOK_URL="\${PUBLIC_URL}\${FORMATTED_WEBHOOK_PATH}"

echo "Webhook Initializer: Setting Telegram webhook to: \$FULL_WEBHOOK_URL"

API_RESPONSE=\$(curl -s -X POST "https://api.telegram.org/bot\${TELEGRAM_BOT_TOKEN}/setWebhook"      -H "Content-Type: application/json"      -H "User-Agent: DockerWebhookInit/1.0"      -d "{\"url\": \"\$FULL_WEBHOOK_URL\"}")

if echo "\$API_RESPONSE" | grep -q '"ok":true'; then
    echo "Webhook Initializer: Webhook set successfully!"
    echo "Webhook Initializer: Telegram API Response: \$API_RESPONSE"
    echo "Webhook Initializer: Public URL is: \$PUBLIC_URL"
    echo "Webhook Initializer: Full webhook URL is: \$FULL_WEBHOOK_URL"
    echo "Webhook Initializer: Localtunnel (PID: \$LT_PID) is running in this container."
    echo "Webhook Initializer: This container will keep running to maintain the tunnel."
    rm -f "\$LT_OUTPUT_FILE" # Clean up before waiting
    wait \$LT_PID
    echo "Webhook Initializer: Localtunnel process ended. Exiting."
else
    echo "Webhook Initializer: Error setting webhook with Telegram!"
    echo "Webhook Initializer: Telegram API Response: \$API_RESPONSE"
    echo "Webhook Initializer: Stopping localtunnel (PID: \$LT_PID)."
    kill \$LT_PID
    wait \$LT_PID 2>/dev/null
    rm -f "\$LT_OUTPUT_FILE"
    exit 1
fi

exit 0
