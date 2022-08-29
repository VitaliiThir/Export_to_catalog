<?php

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Request;
use PHPOffice\App\Util;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require('../../PHPOffice/vendor/autoload.php');

?>
<?php if (Request::is_ajax() && AdminShop::get_shop_id() !== null): ?>
    <form class="load-file" enctype="multipart/form-data">
        <input type="hidden" name="action" value="files">
        <div class="form-group mb-4">
            <input type="file" name="user_file" id="user_file" class="form-control-file">
        </div>

        <div class="file-actions">
            <button type="submit" class="btn btn-primary send-file-btn mr-2">Сохранить</button>
            <?php Util::loader() ?>
        </div>
    </form>
    <script src="/<?= Config::$shops_root_dir ?>/assets/scripts/checkout.js"></script>
<?php else: ?>
    <?php LocalRedirect('404.php'); ?>
<?php endif; ?>
