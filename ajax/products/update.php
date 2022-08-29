<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Products\ShopProductsEdit;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

$request = Request::http_request();

if (Request::is_ajax() && AdminShop::get_shop_id() !== null && Request::http_request()->getPost('update_data')) try {
    $data = Request::http_request()->getPost('update_data');

    $actions = new ShopProductsEdit($data);

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}