<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Request;
use PHPOffice\App\Shop\ShopProfile;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

require('../../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null) {
    $shop_profile_arr = new ShopProfile();
} else {
    LocalRedirect('404.php');
}