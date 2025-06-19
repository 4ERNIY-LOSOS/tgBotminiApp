<?php
namespace App\Shop\Cart\Controllers;

use App\Shop\Common\BaseShopController;

class CartController extends BaseShopController
{
    // Cart actions (view, add, update, remove - primarily for Mini App API)
    public function view() {}
    public function addToCartMiniApp() {} // Example for Mini App
    public function add() {} // Example for Telegram command /cart_add
}
