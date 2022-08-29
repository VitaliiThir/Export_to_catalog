<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Excel\ExcelModeration;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null && Request::http_request()->getPost('moder_file_path') != '') try {
    $file_path = Request::http_request()->getPost('moder_file_path');
    $request_type = Request::http_request()->getPost('moder_submit');
    $excel_moderation = new ExcelModeration($file_path);

    if ($request_type == 'moder-cancel') {

        $excel_moderation->get_cancel('moder_delete_ib_el');

    } else if ($request_type == 'moder-send') {

        $excel_moderation->get_send('date_create');

    }

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}
