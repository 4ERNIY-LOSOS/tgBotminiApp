# Order Module (`app/Shop/Order/`)

This module is responsible for managing all aspects of customer orders within the e-commerce platform. It handles the process from the point a user decides to checkout with items from their cart, through payment (if applicable), to order confirmation and subsequent tracking.

## Key Responsibilities:

*   **Checkout Process:** Guiding the user through the steps of providing shipping information, delivery preferences, and payment details.
*   **Order Creation:** Taking the contents of the cart and user details to create a formal order record in the system.
*   **Payment Integration:** Interacting with payment gateways to process payments for orders. (Actual gateway logic might be in a separate service or a common payment module).
*   **Order Persistence:** Storing order details (items, quantities, prices, customer information, shipping address, order status) in the database.
*   **Order Status Management:** Tracking the status of an order as it moves through different stages (e.g., pending payment, processing, shipped, delivered, completed, cancelled, refunded).
*   **Order History:** Allowing users to view their past orders and their current statuses.
*   **Notifications:** Sending order confirmation emails/messages to customers and notifications to administrators about new orders.
*   **Inventory Adjustment:** Decreasing stock levels for purchased items (often in coordination with the Product module/service).

## Structure:

This module typically includes:

*   **`Controllers/`**: (`OrderController.php`) Handles HTTP requests related to orders, such as initiating checkout, placing an order (often via API from the Mini App), and viewing order history.
*   **`Models/`**:
    *   `Order.php`: Represents an order in the database, storing header-level information.
    *   `OrderItem.php`: Represents individual items within an order, linked to the `Order` model.
*   **`Services/`**: (`OrderService.php`) Contains the core business logic for order processing. This includes creating orders, interacting with payment services, updating order statuses, managing inventory adjustments post-order, and handling notifications.
*   **`Events/`**: (If applicable) Events related to orders, such as `OrderPlacedEvent`, which other parts of the system can listen to (e.g., to send emails or update analytics).

The Order module is critical for the transactional aspect of the e-commerce platform and ensures that customer purchases are accurately recorded and processed.
