# Common Module (`app/Shop/Common/`)

The Common module within `app/Shop/` serves as a central place for shared code, utilities, base classes, interfaces, traits, and services that are utilized by multiple other submodules of the Shop (e.g., Product, Cart, Order, Admin).

## Purpose:

The primary goal of this module is to:

*   **Promote Reusability:** Avoid code duplication by providing common functionalities in one place.
*   **Improve Maintainability:** Make it easier to update shared logic as changes only need to be made in one location.
*   **Enhance Organization:** Keep the Shop module's structure clean by separating general-purpose components from domain-specific logic.

## Potential Components:

*   **Base Classes:**
    *   `BaseShopController.php`: A base controller that other shop controllers can extend to inherit common functionality, such as user authentication checks, initialization of shared services, or common view data preparation.
    *   `BaseShopService.php`: (If applicable) A base service class.
*   **Services:**
    *   `TelegramService.php`: A service to encapsulate interactions with the Telegram Bot API (sending messages, handling updates, etc.), used by various modules that need to communicate with Telegram.
    *   `ShopLogger.php`: (If a specific logger for shop activities is needed) A dedicated logging service.
    *   Utility services for tasks like currency formatting, date manipulation specific to shop needs, etc.
*   **Interfaces and Traits:** Common interfaces or traits that define contracts or provide reusable behavior for other classes in the Shop module.
*   **Helper Functions:** Utility functions that are broadly used across the shop's backend.
*   **Middleware:** (If not placed in a top-level `app/Middlewares` or a more specific Shop middleware directory) Common middleware for shop routes, e.g., `UserAuthMiddleware`, `AdminAuthMiddleware`. The initial project structure suggests these might be in `hleb/app/Middlewares/` or a more specific `hleb/app/Shop/Common/Middlewares/` if that level of granularity is desired.

By centralizing these common elements, the rest of the Shop submodules can focus on their specific responsibilities more effectively.
