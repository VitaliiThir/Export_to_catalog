<?php

namespace PHPOffice\App\Excel;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Props;
use PHPOffice\App\Request;

class ExcelModerationList extends ExcelMain
{

    public function __construct()
    {
        try {
            $this->get_moderation_list();
        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }

    /**
     * @throws LoaderException
     */
    private function get_moderation_list()
    {
        Loader::includeModule('iblock');
        $shop_id = AdminShop::get_shop_id();
        $moder_db = Props::getPropsFileData(Config::$shop_ib_id, $shop_id, ['ID', 'PROPERTY_MODER_FILE', 'PROPERTY_FILE_DATE_MODER']);
        $file_path = $moder_db['PROPERTY_MODER_FILE_VALUE'];
        $date_create = $moder_db['PROPERTY_FILE_DATE_MODER_VALUE'];

        if ($file_path == '') {
            Request::json_response(
                'info',
                'У вас нет товаров на модерации'
            );
        }

        $cells = $this->excel_to_array($_SERVER['DOCUMENT_ROOT'] . $file_path);
        $products_rows = '';

        foreach ($cells as $cell_key => $item) {
            if ($cell_key > 0) {
                $row = '';

                foreach ($item as $item_key => $key) {
                    if ($item_key == 1) {
                        $row .= "<td class='text-start'>$key</td>";
                    } else {
                        $row .= "<td>$key</td>";
                    }
                }

                $products_rows .= "<tr>$row</tr>";

            }
        }

        echo $this->moderation_list_table($file_path, $date_create, $products_rows);
    }

    /**
     * @param $file_path
     * @param $date_create
     * @param $products_rows
     * @return string
     */
    private function moderation_list_table($file_path, $date_create, $products_rows): string
    {
        $cells_head_need_cnt = count($this->cells_head_need);

        return "<table class='user-table table table-hover table-light'>
                    <thead>
                    <tr>
                        <td colspan='$cells_head_need_cnt'>
                            <div class='alert alert-warning text-start'>
                                Отменить модерацию можно только до начала проверки модератором, иначе функция будет недоступна.<br>
                                По окончанию модерации вы получите уведомление о её результате.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th colspan='$cells_head_need_cnt'>
                            <div class='w-100 d-inline-flex justify-content-between align-items-center'>
                                <span class='text-danger text-start'>Товары на модерации:<br>
                                    <small class='text-body font-weight-normal'>Дата добавления: $date_create</small>
                                </span>
                                <div class='moder-btns d-flex align-items-center'>
                                    <a href='$file_path' class='btn btn-success me-1' download>Скачать</a>
                                    <form class='moderation-form'>
                                        <input type='hidden' name='moder_file_path' value='$file_path'>
                                        <input type='hidden' name='moder_delete_ib_el' value='ok'>
                                        <input type='hidden' name='moder_submit' value='moder-cancel'>
                                        <button type='submit' class='btn btn-danger btn-moder-cancel'>Отменить модерацию</button>
                                    </form>
                                    <script src='/" . Config::$shops_root_dir . "/assets/scripts/moderation.js'></script>
                                </div>
                            </div>
                        </th>
                    </tr>
                        <tr>
                            {$this->table_head_html()}
                        </tr>
                    </thead>
                    <tbody>
                        $products_rows
                    </tbody>
             </table>";
    }
}