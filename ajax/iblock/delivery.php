<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Request;
use PHPOffice\App\Shop\ShopProfile;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null) try {

    ShopProfile::get_prop_delivery_html();

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}
