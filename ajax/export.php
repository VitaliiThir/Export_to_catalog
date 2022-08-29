<?php

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Excel\ExcelExport;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../PHPOffice/vendor/autoload.php');

$request = Request::http_request();

if (Request::is_ajax() && AdminShop::get_shop_id() !== null && $request->getPost('action') == 'user-export') try {
    $file_path = $request->getPost('user_export_file');

    if ($file_path != '') {
        $excel_export = new ExcelExport($file_path);

        if ($request->getPost('export-status') == 'send') {
            $excel_export->set_file_result();
        }

        if ($request->getPost('export-status') == 'cancel') {
            $excel_export->export_cancel();
        }
    }

} catch (LoaderException $e) {
    Debug::dump($e->getMessage());
}