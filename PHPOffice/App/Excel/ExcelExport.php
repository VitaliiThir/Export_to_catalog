<?php

namespace PHPOffice\App\Excel;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Props;
use PHPOffice\App\Request;
use PHPOffice\App\Util;

class ExcelExport extends ExcelMain
{

    /**
     * @var string|mixed
     */
    private string $shop_id;

    /**
     * @var array
     */
    private array $date;

    /**
     * @var string
     */
    private string $request_file_path;

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->request_file_path = $file;
        $this->shop_id = AdminShop::get_shop_id();
        $this->date = $this->get_current_date_time();
    }

    /**
     * @return string[]
     */
    private function get_moderation_paths_arr(): array
    {
        $file_ext = $this->get_file_extension($this->request_file_path);
        // Директория для модерации
        $file_moderation_dir = "/" . Config::$shops_root_dir . "/files/shop_{$this->shop_id}/moderation";

        return [
            'dir' => $file_moderation_dir,
            'dir_with_file' => "$file_moderation_dir/moderation__shop_{$this->shop_id}" . "__{$this->date['file']}" . ".$file_ext"
        ];

    }

    /**
     * @return void
     * @throws LoaderException
     */
    public function set_file_result()
    {
        $cells = $this->excel_to_array($this->request_file_path);

        $file_moderation_dir = $this->get_moderation_paths_arr()['dir'];
        $file_path_moderation = $this->get_moderation_paths_arr()['dir_with_file'];

        $this->get_check_file($cells, $file_moderation_dir, $file_path_moderation);
    }

    /**
     * @throws LoaderException
     */
    private function get_check_file($cells, $file_moderation_dir, $file_path_moderation)
    {
        // Проверка заполненных данных файла
        $required_field_index = Config::$table_column_index_required; // Индекс поля таблицы для проверки - на уникальность и корректность
        $required_field_ids = [];
        $products_rows = '';
        $products_moderation_arr = [
            $this->cells_head_need
        ];
        $products_searched = [];

        foreach ($cells as $key_row => $row) {
            if ($key_row > 0) {
                $required_field_ids[] = trim($row[$required_field_index]);
            }
        }

        if (!empty($required_field_ids)) {
            Loader::includeModule('iblock');
            // Выбираем товары по артикулу
            $products_db = CIBlockElement::GetList(
                ['name' => 'asc'],
                ['IBLOCK_ID' => Config::$catalog_ib_id, 'PROPERTY_ARTNUMBER' => $required_field_ids],
                false, false,
                []
            );

            while ($product = $products_db->GetNextElement()) {
                $prod_props = $product->GetProperties();

                $products_searched[] = $prod_props['ARTNUMBER']['VALUE'];

                // Код добавления товара
            }

        }

        foreach ($cells as $cell_key => $item) {
            if ($cell_key > 0) {
                if (!in_array($item[$required_field_index], $products_searched)) {
                    $row = '';

                    $products_moderation_arr[] = $item;

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
        }

        // Удаляем активный файл
        unlink($this->request_file_path);

        // Очищаем поля свойств файла магазина
        Props::setPropsFileData($this->shop_id, Config::$shop_ib_id,
            [
                'FILE_NAME' => '',
                'FILE_DATE' => '',
                'FILE_PATH' => ''
            ]);

        $ending_words_arr_before = ['добавлен', 'добавлено', 'добавлено'];
        $ending_words_arr_after = ['товар', 'товара', 'товаров'];
        $products_searched_cnt = count($products_searched);
        $catalog_added_info = "В каталог "
            . Util::ending_word($products_searched_cnt, $ending_words_arr_before)
            . "  <b>$products_searched_cnt</b> "
            . Util::ending_word($products_searched_cnt, $ending_words_arr_after);

        if (count($products_moderation_arr) > 1) {

            if (!empty(glob($_SERVER['DOCUMENT_ROOT'] . "$file_moderation_dir/*.*"))) {
                Util::clear_files($_SERVER['DOCUMENT_ROOT'] . "$file_moderation_dir");
            }

            $this->create_excel($products_moderation_arr, $file_path_moderation);

            $status_text = $catalog_added_info . ".<br>"
                . "Список товаров текущего магазина находится в разделе <b>«" . Config::$tabs_arr['catalog']['name'] . "».</b>";

            Request::json_response(
                'ok',
                $status_text,
                '',
                [],
                $this->response_table($file_path_moderation, $products_rows),
                $file_path_moderation
            );

        } else {

            Request::json_response(
                'ok',
                $catalog_added_info
            );
        }
    }

    /**
     * @param $file_path_moderation
     * @param $products_rows
     * @return string
     */
    private function response_table($file_path_moderation, $products_rows): string
    {
        $cells_head_need_cnt = count($this->cells_head_need);
        $full_date_for_ib = $this->date['iblock'];
        $table_head = $this->table_head_html();

        return sprintf("
                <table class='user-table table table-hover table-light'>
                    <thead>
                        <tr>
                            <th colspan='$cells_head_need_cnt' class='text-start'>
                                <div class='w-100 d-inline-flex justify-content-between align-items-center'>
                                    <div class='alert alert-warning py-1 flex-grow-1 me-2'>
                                        (!) Некоторые позиции в каталоге «ВПалитре» не найдены.<br>
                                        &nbsp;&nbsp;&nbsp;&nbsp;Для создания новых позиций требуется модерация.
                                    </div>      
                                    <div class='moder-btns d-flex'>
                                        <form class='moderation-form me-1'>
                                            <input type='hidden' name='moder_file_path' value='$file_path_moderation'>
                                            <input type='hidden' name='date_create' value='$full_date_for_ib'>
                                            <input type='hidden' name='moder_submit' value='moder-send'>
                                            <button type='submit' class='btn btn-success btn-moder-send'>Отправить на модерацию</button>
                                        </form>
                                        <a href='$file_path_moderation' class='btn btn-success me-1' download>Скачать</a>
                                        <form class='moderation-form'>
                                            <input type='hidden' name='moder_file_path' value='$file_path_moderation'>
                                            <input type='hidden' name='moder_submit' value='moder-cancel'>
                                            <button type='submit' class='btn btn-danger btn-moder-cancel'>Завершить</button>
                                        </form>
                                    </div>
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <td colspan='$cells_head_need_cnt' class='text-start'>
                                <div class='alert alert-info py-1 mb-0'>
                                    <b>*</b>Отслеживать результаты модерации можно в разделе <b>«%s»</b>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            $table_head
                        </tr>
                    </thead>
                    <tbody>
                        $products_rows
                    </tbody>
               </table><script src='/%s/assets/scripts/moderation.js'></script>", Config::$tabs_arr['moderation']['name'], Config::$shops_root_dir);
    }

    /**
     * @return array
     */
    public static function check(): array
    {
        $shop_id = AdminShop::get_shop_id();
        $props_data = Props::getPropsFileData(
            Config::$shop_ib_id,
            $shop_id,
            ['ID', 'PROPERTY_FILE_NAME', 'PROPERTY_FILE_DATE', 'PROPERTY_FILE_PATH']
        );
        $user_file = [
            'NAME' => $props_data['PROPERTY_FILE_NAME_VALUE'],
            'DATE' => $props_data['PROPERTY_FILE_DATE_VALUE'],
            'PATH' => $props_data['PROPERTY_FILE_PATH_VALUE'],
        ];


        if ($user_file['PATH'] != '') {
            $root_user_file = $_SERVER['DOCUMENT_ROOT'] . $user_file['PATH'];

            if (!file_exists($root_user_file)) {
                Props::setPropsFileData($shop_id, Config::$shop_ib_id, [], true);
                $user_file = [];
            }
        }

        return $user_file;
    }

    public function export_cancel()
    {
        Props::setPropsFileData($this->shop_id, Config::$shop_ib_id, [], true);

        unlink($this->request_file_path);

        Request::json_response(
            'ok',
            'Экспорт отменён'
        );
    }
}