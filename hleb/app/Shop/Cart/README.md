# Cart Module (`app/Shop/Cart/`)

This module is responsible for all functionalities related to the user's shopping cart. It allows users to collect items they intend to purchase before proceeding to checkout. This module is crucial for the Telegram Mini App, providing the backend logic for its cart interface.

## Key Responsibilities:

*   **Adding Items:** Allowing users to add products to their cart, specifying quantity and potentially product variations.
*   **Viewing Cart:** Displaying the current contents of the cart, including items, quantities, individual prices, and the total price.
*   **Updating Quantities:** Enabling users to change the quantity of items in their cart.
*   **Removing Items:** Allowing users to remove items from their cart.
*   **Cart Persistence:** Managing the cart's state, which could be session-based for guest users or stored in the database for registered users.
*   **Calculating Totals:** Computing the subtotal, taxes (if applicable), shipping costs (if integrated here), and the final grand total for the items in the cart.
*   **Coupon/Discount Application:** (If applicable) Applying discount codes or promotions to the cart total.

## Structure:

This module typically includes:

*   **`Controllers/`**: (`CartController.php`) Handles HTTP requests, primarily API calls from the Telegram Mini App, for cart operations (e.g., `addToCartMiniApp`, `viewCart`, `updateItem`, `removeItem`). It might also handle Telegram commands like `/cart`.
*   **`Services/`**: (`CartService.php`) Contains the core business logic for cart management. This service interacts with product data (to get prices, check stock), manages cart storage (session or database), and performs calculations.
*   **`Models/`**: (If the cart is stored in the database or needs complex data structures) Models to represent the cart and its items. For session-based carts, models might not be strictly necessary here.

The `CartController` will expose API endpoints that the JavaScript frontend of the Telegram Mini App consumes to provide a dynamic and interactive cart experience.
