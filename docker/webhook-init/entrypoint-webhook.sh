#!/bin/bash

echo "--- Webhook Initializer Service ---"
echo "This service is currently configured for manual localtunnel and webhook setup."
echo "Please refer to the main README.md for instructions on how to:"
echo "1. Start localtunnel manually."
echo "2. Set your Telegram bot webhook using the localtunnel URL."
echo "3. Configure the MINI_APP_BASE_URL in your .env file."
echo ""
echo "This service will now idle. It no longer performs automated tasks."
echo "Timestamp: $(date)"

# Keep the container running if needed, or it can just exit.
# For simplicity, let it idle.
while true; do sleep 3600; done
