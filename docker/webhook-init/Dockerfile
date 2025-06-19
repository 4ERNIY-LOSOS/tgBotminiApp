# Use a lightweight Node.js image
FROM node:18-alpine

# Install necessary packages: curl for API calls, bash for more robust scripting (optional, sh might be enough)
# git is sometimes a dependency for npm packages or for debugging.
RUN apk add --no-cache curl bash git

# Install localtunnel globally
RUN npm install -g localtunnel

# Create a directory for the entrypoint script
WORKDIR /app
COPY entrypoint-webhook.sh .

# Make the entrypoint script executable
RUN chmod +x entrypoint-webhook.sh

# Set the entrypoint
ENTRYPOINT ["/app/entrypoint-webhook.sh"]
