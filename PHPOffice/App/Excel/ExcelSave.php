<?php

namespace PHPOffice\App\Excel;

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Props;
use PHPOffice\App\Request;
use PHPOffice\App\Util;

class ExcelSave extends ExcelMain
{

    /**
     * @var string
     */
    protected string $root_shop_dir_name = 'shop';

    /**
     * @param string $file_name
     */
    public function __construct(string $file_name = '')
    {
        $this->check_extension_file($file_name, Config::$required_extensions);
        $this->create_save_dir_and_upload_file(AdminShop::get_shop_id(), $this->root_shop_dir_name, $file_name);
    }

    /**
     * @param $file_name
     * @param array $extensions
     * @return void
     */
    public function check_extension_file($file_name, array $extensions = array())
    {
        // Проверка расширения файла
        $file_ext = $this->get_file_extension($file_name);
        $need_ext = $extensions; // Требуемые расширения

        // Если расширение некорректное, возвращаем уведомление об ошибке
        if (!in_array($file_ext, $need_ext)) {
            Request::json_response(
                'fail',
                'Неверное расширение файла!' . '<br>'
                . 'Ваш файл с расширением - <b>' . $file_ext . '</b><br>'
                . 'Требуемые расширение - <b>' . implode(" | ", $need_ext) . '</b>'
            );
        }
    }

    /**
     * @param $shop_id
     * @param $root_shop_dir_name
     * @param $file_name
     * @return void
     */
    public function create_save_dir_and_upload_file($shop_id, $root_shop_dir_name, $file_name)
    {
        // Локально-корневая директория (относительный путь)
        $local_root_dir = "/" . Config::$shops_root_dir . "/files";
        // Директория для хранения файлов (абсолютный путь)
        $root_dir = $_SERVER['DOCUMENT_ROOT'] . $local_root_dir;
        // Директория пользователя с указанием ID (абсолютный путь)
        $shop_dir = "$root_dir/$root_shop_dir_name" . "_$shop_id";
        // Путь к директории для хранения текущего файла (абсолютный путь)
        $shop_root_active_dir = "$root_dir/$root_shop_dir_name" . "_$shop_id/active";
        // Путь к директории для хранения текущего файла (относительный путь)
        $shop_local_active_dir = "$local_root_dir/$root_shop_dir_name" . "_$shop_id/active";
        // Путь к директории для хранения файла на модерации (абсолютный путь)
        $shop_root_moderation_dir = "$root_dir/$root_shop_dir_name" . "_$shop_id/moderation";

        $date = $this->get_current_date_time();

        // Добавляем число, время и название файла при сохранении в директорию
        $base_name = basename($file_name);
        $full_file_name = $date['file'] . '__' . $base_name;
        $local_root_full_path = $shop_local_active_dir . '/' . $full_file_name;
        $upload = $shop_root_active_dir . '/' . $full_file_name;

        // Если директория текущего пользователя не существует, создать
        Util::create_directory($shop_dir);

        // Если директории для файла модерации нет, создать
        Util::create_directory($shop_root_moderation_dir);

        // Если директория для активного файла нет, создать
        Util::create_directory($shop_root_active_dir);

        // Очистка директории для текущего файла
        Util::clear_files($shop_root_active_dir);

        // Сохраняем файл
        move_uploaded_file($_FILES['shop_file']["tmp_name"], $upload);

        // Если файл добавился в директорию, возвращаем ответ
        $this->return_response_status($upload, $base_name, $local_root_full_path, $date['iblock']);
    }

    /**
     * @param $upload
     * @param $base_name
     * @param $local_root_full_path
     * @param $full_date_for_ib
     * @return void
     */
    public function return_response_status($upload, $base_name, $local_root_full_path, $full_date_for_ib)
    {
        if (file_exists($upload)) {

            Request::json_response(
                'ok',
                'Файл проверяется!',
                '',
                [
                    'NAME' => $base_name,
                    'PATH' => $local_root_full_path,
                    'DATE' => $full_date_for_ib
                ]
            );

        } else {
            Request::json_response(
                'fail',
                'Ошибка сохранения файла'
            );
        }
    }

    /**
     * @param $shop_id
     * @return void
     */
    public static function check_has_active($shop_id)
    {
        $shop_dir = $_SERVER['DOCUMENT_ROOT'] . "/" . Config::$shops_root_dir . "/files/shop_$shop_id/active";

        if (!empty(glob("$shop_dir/*.*"))) {
            echo '<div class="alert alert-warning mb-2"><b>(!)</b> У вас есть сохранённый файл для экспорта в каталог.<br>
                      В случае сохранения нового файла, предыдущий файл перезапишется.<br>
                      Действия по экспорту товаров в каталог выполняются в разделе <b>«' . Config::$tabs_arr['export']['name'] . '»</b>.</div>';
        }

    }

    /**
     * @param $shop_id
     * @return void
     */
    public static function check_has_moderation($shop_id)
    {
        $moder_db = Props::getPropsFileData(Config::$shop_ib_id, $shop_id, ['ID', 'PROPERTY_MODER_FILE']);
        $file_path = $moder_db['PROPERTY_MODER_FILE_VALUE'];

        if ($file_path != '') {
            Request::json_response(
                'fail',
                "<b>Отказано в доступе!</b><br>
                      У вас есть товары на модерации из предыдущего запроса.<br>
                      Дождитесь результата текущей модерации или отмените её.<br>
                      Отслеживать модерацию можно в разделе <b>«Товары на модерации»</b>."
            );
        }
    }
}