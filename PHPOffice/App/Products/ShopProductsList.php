<?php

namespace PHPOffice\App\Products;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CCatalogSku;
use CIBlockElement;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Request;

class ShopProductsList
{
    /**
     * @var string
     */
    protected string $shop_id;

    public function __construct()
    {
        $this->shop_id = AdminShop::get_shop_id();

        try {
            $this->get_list();
        } catch (LoaderException $e) {
            Debug::dump($e->getMessage());
        }
    }

    /**
     * @throws LoaderException
     */
    public function get_list()
    {
        $offers_products_data = $this->get_products_data();

        if (!empty($offers_products_data)) {

            echo $this->table_generate($offers_products_data);

        } else {
            Request::json_response(
                'info',
                'Ваш список товаров пуст'
            );
        }
    }

    /**
     * @return array
     * @throws LoaderException
     */
    private function get_products_ids(): array
    {
        Loader::includeModule('iblock');
        $offers_products_ids = array();
        $offers_els_db = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => 3, 'PROPERTY_SHOP_ID' => $this->shop_id],
            false, false,
            array('ID', 'PROPERTY_CML2_LINK')
        );

        while ($offer_el = $offers_els_db->Fetch()) {
            $offer_parent_id = $offer_el['PROPERTY_CML2_LINK_VALUE'];

            $offers_products_ids[] = $offer_parent_id;
        }

        return $offers_products_ids;
    }

    /**
     * @return array
     * @throws LoaderException
     */
    private function get_products_data(): array
    {
        Loader::includeModule('catalog');
        $offers_products_ids = $this->get_products_ids();
        $offers_products_data = array();
        $offers_products_props = $this->get_parent_product_props($offers_products_ids);
        $offers_db = CCatalogSKU::getOffersList(
            $offers_products_ids,
            0,
            array('PROPERTY_SHOP_ID' => $this->shop_id),
            array('ID', 'ACTIVE', 'IBLOCK_ID', 'NAME', 'CATALOG_PRICE_1', 'DATE_CREATE'),
            array('CODE' => array())
        );

        foreach ($offers_db as $key => $offer) {
            $offers_products_data[$key]['ID'] = $offer[array_key_first($offer)]['ID'];
            $offers_products_data[$key]['NAME'] = $offer[array_key_first($offer)]['NAME'];
            $offers_products_data[$key]['ARTICLE'] = $offers_products_props[$offer[array_key_first($offer)]['PARENT_ID']];
            $offers_products_data[$key]['QUANTITY'] = $offer[array_key_first($offer)]['CATALOG_QUANTITY'];
            $offers_products_data[$key]['PRICE'] = (int)$offer[array_key_first($offer)]['CATALOG_PRICE_1'];
            $offers_products_data[$key]['ACTIVE'] = $offer[array_key_first($offer)]['ACTIVE'] == 'Y' ? 'checked' : '';
            $offers_products_data[$key]['DATE_CREATE'] = $offer[array_key_first($offer)]['DATE_CREATE'];
        }

        return $offers_products_data;
    }

    /**
     * @param $products_ids
     * @return array
     */
    private function get_parent_product_props($products_ids): array
    {
        $product_props_arr = [];
        $products_db = CIBlockElement::GetList([], ['IBLOCK_ID' => Config::$catalog_ib_id, 'ACTIVE' => 'Y', 'ID' => $products_ids], false, false, ['ID', 'PROPERTY_ARTNUMBER']);

        while ($product = $products_db->GetNext()) {
            $product_props_arr[$product['ID']] = $product['PROPERTY_ARTNUMBER_VALUE'];
        }

        return $product_props_arr;
    }

    /**
     * @param $offers_products_data
     * @return string
     */
    private function table_generate($offers_products_data): string
    {
        $rows = '';

        foreach ($offers_products_data as $item) {
            $id = $item['ID'];
            $row = "<tr data-product>
                        <td class='text-center'>
                            <input type='checkbox'
                                   @change='check_inputs($id)' 
                                   class='form-check-input position-static'
                                   :disabled='edit'
                                   name='products_ids[]'
                                   data-prod-id='$id'
                                   value='$id'>
                        </td>
                        <td data-name='{$item['NAME']}'>
                            {$item['NAME']}
                        </td>
                        <td class='text-center'>{$item['ARTICLE']}</td>
                        <td class='text-center' data-cnt='{$item['QUANTITY']}'>
                            <input type='text'
                                   v-if='edit && get_editable_products($id)'
                                   value='{$item['QUANTITY']}'
                                   class='form-control'
                            >
                            <span v-else>{$item['QUANTITY']}</span>
                        </td>
                        <td class='text-center' data-price='{$item['PRICE']}'>
                            <input type='text'
                                       v-if='edit && get_editable_products($id)'
                                       value='{$item['PRICE']}'
                                       class='form-control'
                            >
                            <span v-else>{$item['PRICE']}</span>
                        </td>
                        <td>
                            <div class='form-check p-0 d-inline-flex justify-content-center w-100'>
                              <input class='form-check-input position-static m-0 mt-1' 
                                     data-active 
                                     :disabled='!edit || !get_editable_products($id)'
                                     type='checkbox'
                                     {$item['ACTIVE']}
                              >
                            </div>
                        </td>
                        <td class='text-center'>{$item['DATE_CREATE']}</td>
                    </tr>";

            $rows .= $row;

        }

        return "<div id='form-products-edit-app'>
                    <form id='form-products-edit'>
                      <input type='hidden' name='action' value='shop-products-list-edit'>
                      <table class='table table-hover table-light table-bordered mb-0'>
                          <thead>
                              <tr>
                                  <th class='bg-primary text-white text-center'>
                                    <input type='checkbox'
                                           @click='check_inputs()'
                                           :checked='active_all'
                                           class='form-check-input position-static'
                                           :disabled='edit'
                                           name='checkall'>
                                  </th>
                                  <th class='bg-primary text-white'>Наименование</th>
                                  <th class='bg-primary text-white text-center'>Артикул</th>
                                  <th class='bg-primary text-white text-center'>Количество</th>
                                  <th class='bg-primary text-white text-center'>Цена ₽</th>
                                  <th class='bg-primary text-white text-center'>Активность</th>
                                  <th class='bg-primary text-white text-center'>Дата добавления</th>
                              </tr>
                          </thead>
                          <tbody>
                              $rows
                          </tbody>
                          </table>
                          <div class='tfooter bg-light p-2 border'>
                                <div class='btns-actions'>
                                    <button type='submit'
                                            v-if='edit'
                                            class='btn btn-success btn-sm'
                                            @click.prevent='on_update'
                                            >Сохранить
                                    </button>
                                    <a href='javascript:void(0)'
                                       v-else
                                       class='btn btn-success btn-sm'
                                       @click.prevent='on_edit'
                                       :class='products_active_ids.length <= 0 ? `disabled` : ``'
                                    >Редактировать</a>
                                    <a href='javascript:void(0)'
                                       v-if='products_active_ids.length > 0'
                                       class='btn btn-danger btn-sm'
                                       @click.prevent='on_delete'
                                       >Удалить</a>
                                    <a href='javascript:void(0)'
                                       v-if='edit'
                                       class='btn btn-secondary btn-sm'
                                       @click.prevent='on_cancel'
                                       >Отмена</a>
                                </div>
                          </div>
                    </form>
                </div>
                <script src='/" . Config::$shops_root_dir . "/assets/scripts/libs/vue@2.7.0.min.js'></script>
                <script src='/" . Config::$shops_root_dir . "/assets/scripts/products-list.js'></script>";
    }
}