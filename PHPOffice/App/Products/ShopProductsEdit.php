<?php

namespace PHPOffice\App\Products;

use PHPOffice\App\Request;
use PHPOffice\App\Util;

class ShopProductsEdit
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->actions();
    }

    /**
     * @return void
     */
    private function actions()
    {
        $errors = $this->is_valid($this->data);

        if (!empty($errors)) {
            $html = "<div class='products-list-errors'><h5><b>В следующих позициях ошибки заполнения данных:</b></h5>";

            foreach ($errors as $arr_item) {
                $ul = "<ul class='list-group list-unstyled'><li><b>{$arr_item["name"]}</b></li>";

                foreach ($arr_item as $key => $field) {
                    if ($key !== "name") $ul .= "<li class='ps-2'><small><b>$key:</b></small> <i>$field</i></li>";
                }

                $ul .= "</ul>";

                $html .= $ul;

            }

            $html .= "</div>";

            Request::json_response(
                'fail',
                $html
            );
        } else {
            Request::json_response(
                'ok',
                'Товары успешно обновлены'
            );
        }
    }

    private function update($data)
    {}

    /**
     * @param $arr
     * @return array
     */
    private function is_valid($arr): array
    {
        $errors = [];
        $mess = [
            'not_empty' => 'Поле не должно быть пустым',
            'is_number' => 'Значение может быть только числом'
        ];

        foreach ($arr as $fields) {
            $error = [];

            foreach ($fields as $key => $field) {
                $keys = [
                    "cnt" => [
                        "title" => "Количество",
                        "required" => true,
                        "is_number" => true
                    ],
                    "price" => [
                        "title" => "Цена",
                        "required" => true,
                        "is_number" => true
                    ]
                ];

                if ($keys[$key]["required"]) {
                    if ($field == "") {
                        $error[$keys[$key]["title"]] = $mess['not_empty'];
                    }
                }

                if ($keys[$key]["is_number"]) {
                    if (Util::test($field)) {
                        $error[$keys[$key]["title"]] = $mess['is_number'];
                    }
                }
            }

            if (!empty($error)) {
                $error = array_merge(array("name" => $fields["name"]), $error);
                $errors[] = $error;
            }

        }

        return $errors;
    }
}