<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Request;
use PHPOffice\App\Shop\ShopUpdate;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../PHPOffice/vendor/autoload.php');

if (
    Request::is_ajax()
    && AdminShop::get_shop_id() !== null
    && Request::http_request()->getPost('action') == 'shop-profile-edit')
    try {

        $shop_update = new ShopUpdate();

        $shop_update->actions();

    } catch (LoaderException $e) {
        Debug::dump($e->getMessage());
    }
