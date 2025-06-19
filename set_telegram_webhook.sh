#!/bin/bash

# --- Configuration ---
# Your Telegram Bot Token (replace with your actual token or use an environment variable)
BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-YOUR_TELEGRAM_BOT_TOKEN_HERE}"
# Local port your Hleb application is running on
LOCAL_PORT="${HLEB_LOCAL_PORT:-5125}"
# Webhook path as defined in your Hleb routes (e.g., /telegram_bot_webhook)
WEBHOOK_PATH="${TELEGRAM_WEBHOOK_PATH:-/telegram_bot_webhook}"
# Localtunnel subdomain preference (optional, might not always be honored by free localtunnel service)
# LT_SUBDOMAIN="yourshop"

# Check if BOT_TOKEN is set, and not the placeholder
if [ -z "\$BOT_TOKEN" ] || [ "\$BOT_TOKEN" == "YOUR_TELEGRAM_BOT_TOKEN_HERE" ]; then
    echo "Error: TELEGRAM_BOT_TOKEN is not set. Please set it in the script or as an environment variable."
    exit 1
fi

# Check if localtunnel is installed
if ! command -v lt &> /dev/null
then
    echo "Error: localtunnel (lt) command could not be found."
    echo "Please install it first, e.g., using 'npm install -g localtunnel'"
    exit 1
fi

echo "Starting localtunnel for port \$LOCAL_PORT..."
echo "Note: If localtunnel asks for a subdomain and you don't have a paid plan, it might not work as expected or might be slow to assign a URL."

# Temporary file to capture localtunnel output
LT_OUTPUT_FILE="lt_output.log"

# Start localtunnel in the background, redirecting all output to the log file
# Using --print-requests false to minimize output, though it might not affect the URL line
lt --port "\$LOCAL_PORT" \${LT_SUBDOMAIN:+--subdomain "\$LT_SUBDOMAIN"} --print-requests false > "\$LT_OUTPUT_FILE" 2>&1 &
LT_PID=\$!

# Give localtunnel some time to start and output the URL
# This duration might need adjustment based on network conditions and localtunnel server responsiveness
echo "Waiting for localtunnel to establish connection and provide URL (approx. 10-15 seconds)..."
# Try to get URL in a loop for a certain duration
PUBLIC_URL=""
for i in {1..15}; do # Try for 15 seconds
    # Try to grep the URL. Common patterns: "your url is: https://..." or just "https://..."
    # This regex tries to find URLs like https://something.loca.lt or https://something.tunnelto.dev (another lt service)
    PUBLIC_URL=\$(grep -o 'https://[a-zA-Z0-9.-]*\(loca\.lt\|tunnelto\.dev\)' "\$LT_OUTPUT_FILE")
    if [ ! -z "\$PUBLIC_URL" ]; then
        break
    fi
    sleep 1
done

# Clean up background localtunnel process if URL not found after timeout
if [ -z "\$PUBLIC_URL" ]; then
    echo "Could not automatically get localtunnel URL from '\$LT_OUTPUT_FILE' after 15 seconds."
    echo "Please check '\$LT_OUTPUT_FILE' for errors or manual URL."
    echo "Stopping background localtunnel process (PID: \$LT_PID)."
    kill \$LT_PID
    wait \$LT_PID 2>/dev/null # Suppress "Terminated" message
    rm -f "\$LT_OUTPUT_FILE"
    exit 1
fi

# Take the first URL if multiple are found (should ideally be one)
PUBLIC_URL=\$(echo "\$PUBLIC_URL" | head -n 1)
echo "Successfully obtained Localtunnel URL: \$PUBLIC_URL"

# Construct the full webhook URL
FULL_WEBHOOK_URL="\${PUBLIC_URL}\${WEBHOOK_PATH}"

echo "Setting Telegram webhook to: \$FULL_WEBHOOK_URL"

# Use curl to set the webhook
# Adding User-Agent, as some services might require it.
API_RESPONSE=\$(curl -s -X POST "https://api.telegram.org/bot\${BOT_TOKEN}/setWebhook"      -H "Content-Type: application/json"      -H "User-Agent: BashWebhookSetter/1.0"      -d "{\"url\": \"\$FULL_WEBHOOK_URL\"}")

# Check Telegram API response
if echo "\$API_RESPONSE" | grep -q '"ok":true'; then
    echo "Webhook set successfully!"
    echo "Telegram API Response: \$API_RESPONSE"
    echo ""
    echo "Localtunnel (PID: \$LT_PID) is running in the background."
    echo "Your public URL is: \$PUBLIC_URL"
    echo "Your full webhook URL is: \$FULL_WEBHOOK_URL"
    echo "Press Ctrl+C in the terminal where localtunnel was started directly if you ran it manually there, or use 'kill \$LT_PID' to stop this background instance."
else
    echo "Error setting webhook with Telegram:"
    echo "Telegram API Response: \$API_RESPONSE"
    echo "Stopping background localtunnel process (PID: \$LT_PID)."
    kill \$LT_PID
    wait \$LT_PID 2>/dev/null
    rm -f "\$LT_OUTPUT_FILE"
    exit 1
fi

# Clean up the output file
rm -f "\$LT_OUTPUT_FILE"

# The script will now exit, but localtunnel continues to run in the background.
echo "Script finished. Localtunnel remains active."
