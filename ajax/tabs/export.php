<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Excel\ExcelExport;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

global $USER;
$user_file = ExcelExport::check();

?>
<?php if (Request::is_ajax() && AdminShop::get_shop_id() !== null): ?>
    <?php if ($user_file['NAME'] != '') {
        $root_user_file = $_SERVER['DOCUMENT_ROOT'] . $user_file['PATH'];
        ?>
        <div class="user-file">
            <form id="user-export">
                <input type="hidden" name="action" value="user-export">
                <input type="hidden" name="user_export_file" value="<?= $root_user_file ?>">
                <input type="hidden" name="export-status" value="">
                <div class="file-actions">
                    <table class="table table-hover table-light">
                        <thead>
                        <tr>
                            <th>Имя файла</th>
                            <th>Дата добавления</th>
                            <th style="text-align: right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <a href="<?= $user_file['PATH'] ?>"
                                   class="btn-link"
                                   title="Скачать/Посмотреть"
                                   download
                                ><?= $user_file['NAME'] ?></a>
                            </td>
                            <td><?= $user_file['DATE'] ?></td>
                            <td style="text-align: right">
                                <button type="submit" class="btn btn-success btn-submit-export" data-status="send">Запустить экспорт в каталог</button>
                                <button type="submit" class="btn btn-danger btn-submit-export" data-status="cancel">Отменить экспорт</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        <script src="/<?= Config::$shops_root_dir ?>/assets/scripts/export.js"></script>
    <?php } else Request::json_response(
        "info",
        "У вас нет загруженных файлов для экспорта"
    ); ?>
<?php else: ?>
    <?php LocalRedirect('404.php'); ?>
<?php endif; ?>