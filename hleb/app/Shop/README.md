# Shop Module

This directory (`app/Shop/`) is the core of the e-commerce functionality within the Hleb application. It encapsulates all the features and logic related to the online store, including product management, shopping cart, order processing, user accounts, and administrative functions.

The Shop module is further broken down into submodules, each responsible for a specific domain:

*   **`Admin/`**: Contains the logic for the administration panel, allowing store owners to manage products, categories, orders, users, and other store settings.
*   **`Ai/`**: Houses features related to Artificial Intelligence, such as product recommendation engines, personalized user experiences, or AI-powered chatbots for customer support.
*   **`Cart/`**: Manages the shopping cart functionality, including adding items, updating quantities, removing items, and calculating totals. This module primarily serves the Telegram Mini App.
*   **`Common/`**: Includes shared classes, helper functions, base controllers, or services that are used across multiple submodules within the Shop.
*   **`Order/`**: Handles all aspects of order processing, from checkout initiation to payment integration (if applicable), order creation, status updates, and order history.
*   **`Product/`**: Responsible for managing the product catalog, including product details, categories, inventory levels, search, and filtering capabilities. This module also handles initial Telegram bot interactions like `/start` and webhook processing.
*   **`User/`**: Manages user-related functionalities such as authentication, user profiles, viewing order history, and managing saved preferences like favorites.

Each submodule typically contains its own Controllers, Models, and Services to maintain a clear separation of concerns and promote modularity.
