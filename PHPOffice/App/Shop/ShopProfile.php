<?php

namespace PHPOffice\App\Shop;

use CFile;
use CIBlockElement;
use CIBlockPropertyEnum;
use CModule;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Request;

class ShopProfile
{

    public function __construct()
    {
        if (!CModule::IncludeModule('iblock')) return;
        $this->shop_html();
    }

    /**
     * @return array
     */
    private function get_shop_data(): array
    {
        $shop_data = array();
        $shop_id = AdminShop::get_shop_id();
        $shop_filter = array('IBLOCK_ID' => Config::$shop_ib_id, 'ACTIVE' => 'Y', 'ID' => $shop_id);
        $shop_select = array();
        $shop_sort = array();
        $shop_db = CIBlockElement::GetList($shop_sort, $shop_filter, false, false, $shop_select);

        while ($shop = $shop_db->GetNextElement()) {
            $shop_fields = $shop->GetFields();
            $shop_props = $shop->GetProperties();

            $shop_data = array(
                [
                    'TYPE' => 'S',
                    'NAME' => 'NAME',
                    'EDIT' => 'Y',
                    'TITLE' => 'Название',
                    'VALUE' => $shop_fields['NAME']
                ],
                [
                    'TYPE' => 'S',
                    'NAME' => 'DATE_CREATE',
                    'EDIT' => 'N',
                    'TITLE' => 'Дата создания',
                    'VALUE' => $shop_fields['DATE_CREATE']
                ],
                [
                    'TYPE' => 'S',
                    'NAME' => 'CITY',
                    'EDIT' => 'Y',
                    'TITLE' => 'Город',
                    'VALUE' => $shop_props['CITY']['VALUE']
                ],
                [
                    'TYPE' => 'S',
                    'NAME' => 'ADDRESS_TEXT',
                    'EDIT' => 'Y',
                    'TITLE' => 'Адрес',
                    'VALUE' => $shop_props['ADDRESS_TEXT']['VALUE']
                ],
                [
                    'TYPE' => 'SP',
                    'NAME' => 'PHONE',
                    'EDIT' => 'Y',
                    'TITLE' => 'Телефон',
                    'VALUE' => $shop_props['PHONE']['VALUE']
                ],
                [
                    'TYPE' => 'SE',
                    'NAME' => 'EMAIL',
                    'EDIT' => 'Y',
                    'TITLE' => 'E-mail',
                    'VALUE' => $shop_props['EMAIL']['VALUE']
                ],
                [
                    'TYPE' => 'A',
                    'NAME' => 'TIMEWORK',
                    'EDIT' => 'Y',
                    'TITLE' => 'Время работы',
                    'VALUE' => $shop_props['TIMEWORK']['~VALUE']['TEXT']
                ],
                [
                    'TYPE' => 'FI',
                    'NAME' => 'DETAIL_PICTURE',
                    'EDIT' => 'Y',
                    'TITLE' => 'Фото',
                    'VALUE' => CFile::GetPath($shop_fields['DETAIL_PICTURE'])
                ],
                [
                    'TYPE' => 'LS',
                    'NAME' => 'DELIVERY',
                    'EDIT' => 'Y',
                    'TITLE' => 'Варианты доставки',
                    'VALUE' => $shop_props['DELIVERY']['VALUE']
                ],
                [
                    'TYPE' => 'S',
                    'NAME' => 'COORDS',
                    'EDIT' => 'Y',
                    'TITLE' => 'Место положения (координаты)',
                    'VALUE' => $shop_props['COORDS']['VALUE']
                ]
            );
        }

        return $shop_data;

    }

    /**
     * @return void
     */
    private function shop_html(): void
    {
        $items = $this->generate_to_html();

        if ($items) {
            $table = "<div id='shop-profile-table'>
                          <script src='/" . Config::$shops_root_dir . "/assets/scripts/shop-profile.js'></script>
                          <div class='shop-profile-table-wrapper'>
                              <input type='hidden' name='action' value='shop-profile-edit'>
                              <table class='table table-bordered table-hover table-light'>$items</table>
                              <table class='table'>
                                <tr>
                                    <td class='text-end'>
                                        <button type='button' class='btn btn-primary btn-sm btn-shop-edit'>Редактировать профиль</button>
                                        <button type='button' class='btn btn-primary btn-sm btn-shop-edit-cancel ms-1'>Отмена</button>
                                    </td>
                                </tr>
                              </table>
                          </div>
                      </div>";

            echo $table;

        } else {
            Request::json_response(
                'fail',
                'Ошибка создания структуры профиля магазина.<br>Повторите действия.'
            );
        }

    }

    /**
     * @return string
     */
    public function generate_to_html(): string
    {
        $shop_arr = $this->get_shop_data();

        return $this->table_rows($shop_arr);
    }

    /**
     * @param $arr
     * @return string
     */
    public function table_rows($arr): string
    {
        $html = '';

        foreach ($arr as $item) {
            $el = "<tr><td class='w-25'><b>{$item['TITLE']}</b></td>";
            $data_attrs_not_value = "class='shop-table-values' data-type='{$item['TYPE']}' data-name='{$item['NAME']}' data-edit='{$item['EDIT']}'";
            $data_attrs_with_value = "class='shop-table-values' data-type='{$item['TYPE']}' data-name='{$item['NAME']}' data-edit='{$item['EDIT']}' data-value='{$item['VALUE']}'";

            if (!is_array($item['VALUE'])) {
                if (strpos($item['VALUE'], 'upload')) {
                    $el .= "<td $data_attrs_with_value><div class='shop-profile-img'><img src='{$item['VALUE']}' alt='{$item['TITLE']}'></div></td>";
                } else if ($item['VALUE'] == '') {
                    $el .= "<td $data_attrs_with_value>Нет</td>";
                } else {
                    $el .= "<td $data_attrs_with_value>{$item['VALUE']}</td>";
                }

            } else {
                $el .= "<td $data_attrs_not_value data-value='multi'>";
                foreach ($item['VALUE'] as $k => $sub_item) {
                    if (count($item['VALUE']) !== ($k + 1)) {
                        $el .= "<div data-value='$sub_item'>$sub_item,</div>";
                    } else {
                        $el .= "<div data-value='$sub_item'>$sub_item</div>";
                    }
                }
                $el .= "</td>";
            }

            $el .= "</tr>";

            $html .= $el;
        }

        return $html;

    }

    /**
     * @return void
     */
    public static function get_prop_delivery_html()
    {
        if (!CModule::includeModule('iblock')) return;
        $delivery_arr = [];
        $property_enums = CIBlockPropertyEnum::GetList(array(), array("IBLOCK_ID" => Config::$shop_ib_id, "CODE" => "DELIVERY"));
        $delivery_db = CIBlockElement::GetProperty(Config::$shop_ib_id, AdminShop::get_shop_id(), 'id', 'desc', array('CODE' => 'DELIVERY'));

        while ($ob = $delivery_db->GetNext()) {
            $delivery_arr[] = $ob['VALUE'];
        }

        $check_group = "<div class='form-check-group'>";

        while ($enum_fields = $property_enums->GetNext()) {
            $checked = in_array($enum_fields["ID"], $delivery_arr) ? 'checked' : false;
            $check_group .= "<div class='form-check form-switch'>
                                <input class='form-check-input' type='checkbox' id='{$enum_fields["ID"]}' name='{$enum_fields["PROPERTY_CODE"]}[]' value='{$enum_fields["ID"]}' $checked>
                                <label class='form-check-label' for='{$enum_fields["ID"]}'>{$enum_fields["VALUE"]}</label>
                             </div>";
        }

        $check_group .= "</div>";

        echo $check_group;

        die();
    }

}