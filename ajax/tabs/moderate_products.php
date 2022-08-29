<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Excel\ExcelModerationList;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null) {
    $excel_moderation_list = new ExcelModerationList();
} else {
    LocalRedirect('404.php');
}
