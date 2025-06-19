# Product Module (`app/Shop/Product/`)

The Product module is central to the e-commerce platform, responsible for managing all aspects of the product catalog, including product details, categorization, inventory, and how products are displayed and discovered by users. It also handles initial Telegram bot interactions.

## Key Responsibilities:

*   **Product Catalog Management:** Storing and retrieving detailed information about each product, such as name, description, price, SKU, images, and custom attributes.
*   **Category Management:** Organizing products into a hierarchical structure of categories to facilitate browsing and navigation for users.
*   **Inventory Management:** Tracking stock levels for products (though this might sometimes be a separate, dedicated inventory module/service in larger systems).
*   **Product Display:** Providing data for displaying product listings (e.g., in a catalog view) and individual product detail pages, primarily for the Telegram Mini App.
*   **Search and Filtering:**
    *   `ProductSearchService.php`: Implementing search functionality to allow users to find products based on keywords.
    *   `ProductFilterService.php`: Enabling users to filter products based on various criteria like price range, category, brand, or other attributes.
*   **Telegram Bot Interaction:**
    *   The `ProductController.php` within this module is also responsible for handling initial Telegram bot commands (like `/start`) and processing incoming webhook updates from Telegram. This involves interacting with the `TelegramService` to send messages and display UI elements like the "Open Mini App" button.

## Structure:

This module typically includes:

*   **`Controllers/`**: (`ProductController.php`) Handles HTTP requests for viewing product listings, product details, search results, and filter applications. It also contains the `handleWebhook` method for Telegram bot updates.
*   **`Models/`**: (`Product.php`, potentially `Category.php` if not managed elsewhere) Represent product and category data in the database.
*   **`Services/`**:
    *   `ProductService.php`: Contains the core business logic for managing products, retrieving product data, and interacting with product models.
    *   `ProductSearchService.php`: Encapsulates the logic for searching products.
    *   `ProductFilterService.php`: Encapsulates the logic for filtering products.
*   **`Views/`**: (If any server-side rendering of product pages is done, e.g., for SEO or non-Mini App contexts, these would be in `hleb/resources/views/products/` or similar).

This module forms the backbone of what users see and interact with when browsing the shop's offerings, both via the Mini App and potentially through direct bot interactions.
