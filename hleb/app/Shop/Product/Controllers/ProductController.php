<?php
namespace App\Shop\Product\Controllers;

use App\Shop\Common\BaseShopController; // Assuming BaseShopController exists

class ProductController extends BaseShopController
{
    // Product related actions (catalog, view, search, webhook handler)
    public function index() {}
    public function show(string $id) {}
    public function search() {}
    public function handleWebhook() {} // For Telegram commands like /catalog, /search
    public function listForMiniApp() {} // For Mini App API
}
