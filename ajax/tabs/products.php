<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Products\ShopProductsList;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null) {
    $products_list = new ShopProductsList();
} else {
    LocalRedirect('404.php');
}
