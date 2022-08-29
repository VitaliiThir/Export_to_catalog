<?php

namespace PHPOffice\App;

use CIBlockElement;
use CModule;

class AdminShop
{

    public function __construct()
    {
        if (!$this->check_shop()) {
            LocalRedirect('404.php');
        }
    }

    /**
     * @return mixed
     */
    public static function get_shop_id()
    {
        return $_SESSION['CURRENT_SHOP_ID'];
    }


    /**
     * @return bool
     */
    private function check_shop(): bool
    {
        if (!CModule::IncludeModule('iblock')) return false;

        global $USER;
        global $APPLICATION;
        $user_id = false;

        if (!$USER->IsAuthorized()) {
            unset($_SESSION['CURRENT_SHOP_ID']);
            unset($_SESSION['CURRENT_SHOP_NAME']);
            LocalRedirect('/auth/');
        } else {
            $user_id = $USER->GetID();
        }

        if (isset($_GET['cur_shop']) && $_GET['cur_shop'] != '') {

            $current_shop_id = $_GET['cur_shop'];

            $shop_db = CIBlockElement::GetList(
                array(),
                [
                    'IBLOCK_ID' => Config::$shop_ib_id,
                    'ACTIVE' => 'Y',
                    'ID' => $current_shop_id,
                    false, false,
                    array()
                ]);

            if ($shop = $shop_db->GetNextElement()) {
                $shop_owner = $shop->GetProperties()['SHOP_OWNER']['VALUE'];

                if ($shop_owner == $user_id) {
                    $shop_name = $shop->GetFields()['NAME'];

                    $_SESSION['CURRENT_SHOP_ID'] = $current_shop_id;
                    $_SESSION['CURRENT_SHOP_NAME'] = $shop_name;

                    $APPLICATION->SetTitle($_SESSION['CURRENT_SHOP_NAME']);

                    return true;

                }

            }

        }

        return false;

    }
}