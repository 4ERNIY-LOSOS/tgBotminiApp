# Telegram Mini App Directory (`hleb/public/shop_mini_app/`)

This directory contains all the static front-end assets for the Telegram Mini App (TMA) associated with the e-commerce platform. Telegram Mini Apps are web applications that run inside the Telegram interface, offering a rich and interactive user experience.

## Contents:

*   **`index.html`**: The main entry point for the Mini App. This HTML file structures the application and typically loads the necessary CSS and JavaScript files.
*   **`css/`**: Contains stylesheets (e.g., `main.css`) to define the visual appearance and layout of the Mini App.
*   **`js/`**: Holds JavaScript files (e.g., `app.js`) that implement the client-side logic, interactivity, and communication with the backend API. This includes:
    *   Fetching product data.
    *   Managing the cart display and interactions.
    *   Handling user input for checkout.
    *   Communicating with the Telegram SDK for Mini Apps (e.g., to get user data, theme colors, or use Telegram's native UI elements).
*   **`images/`**: (If any static images are part of the Mini App's UI itself, not product images which would be loaded dynamically).

## Functionality:

The files in this directory are served statically by the web server (Nginx). The Mini App is launched within Telegram when a user clicks a special button or link provided by the bot.

The JavaScript within the Mini App (`app.js`) will make API calls to the Hleb backend (defined in `hleb/routes/map.php` and handled by controllers in `hleb/app/Shop/`) to:
*   Fetch product listings and details.
*   Add/remove items from the cart.
*   Retrieve cart contents.
*   Initiate the checkout process.
*   Access user-specific information after authentication/authorization.

## Development:

Frontend development for the Mini App (HTML, CSS, JavaScript) happens here. Developers might use frameworks like React, Vue, Svelte, or vanilla JavaScript to build the application.

The base URL for this Mini App (e.g., `https://yourdomain.com/shop_mini_app/`) is configured in the backend (likely in `.env` and used by `ProductController.php` when sending the WebApp button via the bot).
