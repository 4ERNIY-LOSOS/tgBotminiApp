# Admin Module (`app/Shop/Admin/`)

This module is responsible for the administrative back-office of the e-commerce platform. It provides the necessary interface and functionality for store administrators to manage various aspects of the shop.

## Key Responsibilities:

*   **Product Management:** Creating, reading, updating, and deleting products (CRUD operations). This includes managing product details, pricing, images, and inventory.
*   **Category Management:** Organizing products into categories, allowing for easier navigation and discovery by users.
*   **Order Management:** Viewing and processing customer orders, updating order statuses (e.g., pending, processing, shipped, delivered, cancelled), and managing returns or refunds if applicable.
*   **User Management:** Viewing customer accounts, managing user roles (if any), and potentially handling customer support inquiries.
*   **Dashboard & Reporting:** Displaying key metrics and sales reports to provide insights into the store's performance.
*   **Store Configuration:** Managing general store settings, payment gateway configurations, shipping options, and other administrative parameters.

## Structure:

Typically, this module will contain:

*   **`Controllers/`**: Handle HTTP requests related to admin functions (e.g., `AdminProductController.php`, `AdminOrderController.php`).
*   **`Models/`**: (If specific admin-related data models are needed, otherwise uses models from other modules like Product, Order).
*   **`Services/`**: Contain business logic specific to administrative tasks.
*   **`Views/`**: (If using server-side rendering for the admin panel, located in `hleb/resources/views/admin/` or a similar path) HTML templates for the admin interface.

Access to this module should be restricted to authenticated administrators only, likely through an `AdminAuthMiddleware`.
