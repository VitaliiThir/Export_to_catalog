<?php

session_start();

use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Util;

$current_url = '';
$active_tab = 'profile';

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
include("templates/header.php");
require_once(__DIR__ . '/PHPOffice/vendor/autoload.php');
global $APPLICATION;
$admin_shop = new AdminShop();
$current_url = '/' . Config::$shops_root_dir . '/?cur_shop=' . AdminShop::get_shop_id() . '&tab=';
?>
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="tabs mb-2 d-flex justify-content-between">
                    <?php
                    foreach (Config::$tabs_arr as $tab) {

                        if ($_GET["tab"] == $tab["query"]) {
                            $active_tab = $tab["query"];
                        }

                        echo "<a href='$current_url{$tab["query"]}' class='btn btn-primary btn-lg flex-grow-1' data-tab='btn-{$tab["query"]}'>{$tab["name"]}</a>";
                    }
                    ?>
                </div>
                <div class="status-bar">
                    <div class="notification"></div>
                    <?php Util::loader(); ?>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="response area-loading"></div>
            </div>
        </div>
    </div>
<?php
include("templates/footer.php");
if ($active_tab != '') {
    echo "<script>
            $(function(){
                $('[data-tab=\"btn-$active_tab\"]').trigger('click')
            })
          </script>";
}
?>