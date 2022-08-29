<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Excel\ExcelSave;
use PHPOffice\App\Request;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require("../../PHPOffice/vendor/autoload.php");

$shop_id = AdminShop::get_shop_id();

ExcelSave::check_has_active($shop_id);
ExcelSave::check_has_moderation($shop_id);

?>
<?php if (Request::is_ajax() && $shop_id !== null): ?>
    <form class="load-file" enctype="multipart/form-data">
        <input type="hidden" name="action" value="files">
        <div class="input-group mb-4">
            <input type="file" name="shop_file" id="user_file" class="form-control">
        </div>

        <div class="file-actions">
            <button type="submit" class="btn btn-primary send-file-btn text-uppercase mr-2 px-4">Сохранить</button>
        </div>
    </form>
    <script src="/<?= Config::$shops_root_dir ?>/assets/scripts/checkout.js"></script>
<?php else: ?>
    <?php LocalRedirect('404.php'); ?>
<?php endif; ?>
