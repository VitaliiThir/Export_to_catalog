<?php

namespace PHPOffice\App\Excel;

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Props;
use PHPOffice\App\Request;

class ExcelModeration extends ExcelMain
{

    /**
     * @var string
     */
    private string $shop_id;

    /**
     * @var string
     */
    private string $file_path_local;

    /**
     * @var string
     */
    private string $file_path_absolute;

    /**
     * @param $file_path
     */
    public function __construct($file_path)
    {
        $this->shop_id = AdminShop::get_shop_id();
        $this->file_path_local = $file_path;
        $this->file_path_absolute = $_SERVER['DOCUMENT_ROOT'] . $this->file_path_local;
    }

    /**
     * @param $request
     * @return void
     */
    public function get_cancel($request)
    {
        if (Request::http_request()->getPost($request) == 'ok') {
            Props::setPropsFileData($this->shop_id, Config::$shop_ib_id, [], false, true);
        }

        if (file_exists($this->file_path_absolute)) {
            unlink($this->file_path_absolute);

            Request::json_response(
                'ok',
                'Действия успешно выполнены'
            );
        }
    }

    /**
     * @return void
     */
    public function get_send($date_create)
    {
        $date_create = Request::http_request()->getPost($date_create);

        Props::setPropsFileData($this->shop_id, Config::$shop_ib_id, ['MODER_FILE' => $this->file_path_local, 'FILE_DATE_MODER' => $date_create]);

        Request::json_response(
            'ok',
            'Товары отправлены на модерацию.<br>Остлеживать модерацию можно в разделе <b>«' . Config::$tabs_arr['moderation']['name'] . '»</b>.'
        );
    }
}