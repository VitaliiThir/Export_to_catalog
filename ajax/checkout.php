<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Excel\ExcelCheckout;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../PHPOffice/vendor/autoload.php');

$request = Request::http_request();

if (Request::is_ajax()
    && AdminShop::get_shop_id() !== null
    && $request->getPost('file_name') != ''
    && $request->getPost('file_path') != ''
    && $request->getPost('file_date') != '') try {

    $request_data = [
        'file_date' => $request->getPost('file_date'),
        'file_name' => $request->getPost('file_name'),
        'file_path' => $request->getPost('file_path')
    ];


    $excel_file_check = new ExcelCheckout($request_data);

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}