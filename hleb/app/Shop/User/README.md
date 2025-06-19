# User Module (`app/Shop/User/`)

The User module is responsible for managing user-specific information and functionalities within the e-commerce platform. This includes aspects like user authentication (if applicable beyond Telegram's user ID), user profiles, and personalized features.

## Key Responsibilities:

*   **User Authentication & Identification:** While Telegram provides a user ID, this module might handle additional authentication layers if a separate shop account system is implemented. It would manage user sessions and secure access to user-specific data.
*   **User Profiles:** Storing and managing user profile information, which could include names, contact details (if provided), shipping addresses, and preferences.
*   **Order History:** Allowing registered users to view their past orders (often by linking to the Order module).
*   **Favorites/Wishlist:**
    *   `FavoriteController.php`, `FavoriteService.php`, `Favorite.php`: Enabling users to save products to a "favorites" or "wishlist" for later viewing or purchase.
*   **Personalized Settings:** Managing user-specific settings or preferences related to their shopping experience.
*   **Account Management:** Providing an interface for users to update their profile information, change passwords (if applicable), and manage their account settings.

## Structure:

This module typically includes:

*   **`Controllers/`**: (`FavoriteController.php`, potentially `UserController.php` or `ProfileController.php`) Handles HTTP requests related to user account management, viewing profiles, managing favorites, etc. These would primarily be API endpoints for the Telegram Mini App.
*   **`Models/`**: (`Favorite.php`, potentially `User.php` if a custom user model is needed beyond basic Telegram user data) Represent user data, favorite items, and related information in the database.
*   **`Services/`**: (`FavoriteService.php`, potentially `UserService.php`) Contains the business logic for user registration, login, profile updates, managing favorites, and other user-centric operations.
*   **`Middleware/`**: (If specific to user authentication for the shop, e.g., `UserAuthMiddleware`) Middleware to protect routes that require an authenticated shop user.

This module helps in personalizing the shopping experience and providing users with tools to manage their interactions and history with the store.
