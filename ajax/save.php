<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Excel\ExcelSave;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../PHPOffice/vendor/autoload.php');

if (Request::is_ajax() && AdminShop::get_shop_id() !== null && Request::http_request()->getPost('action') == 'files') try {
    $file_name = $_FILES['shop_file']['name'];

    if ($file_name !== '') {

        $excel_save = new ExcelSave($file_name);

    } else {

        Request::json_response(
            'fail',
            'Вы не добавили файл'
        );

    }
    die();

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}