<?php

namespace PHPOffice\App\Shop;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CIBlockElement;
use PHPOffice\App\AdminShop;
use PHPOffice\App\Config;
use PHPOffice\App\Excel\ExcelMain;
use PHPOffice\App\Request;

class ShopUpdate extends ExcelMain
{
    /**
     * @var array|string[]
     */
    protected array $regex = [
        "email" => "/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u",
        'coords' => '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/'
    ];

    /**
     * @throws LoaderException
     */
    public function actions()
    {
        Loader::includeModule('iblock');
        global $USER;
        $user_id = $USER->GetID();
        $shop_id = AdminShop::get_shop_id();
        $shop_el = new CIBlockElement;
        $errors = [];
        $request_data = [
            "FIELDS" => [
                "name" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("NAME"))),
                    "LABEL" => "Имя",
                    "REQUIRED" => true
                ],
                "image" => [
                    "VALUE" => Request::http_request()->getFile("DETAIL_PICTURE"),
                    "LABEL" => "Фото",
                    "FILE" => [
                        "EXTS" => ["jpg", "jpeg", "png"],
                        "SIZE" => 307200
                    ]
                ],
            ],
            "PROPS" => [
                "city" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("CITY"))),
                    "LABEL" => "Город",
                    "REQUIRED" => true
                ],
                "address" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("ADDRESS_TEXT"))),
                    "LABEL" => "Адрес",
                    "REQUIRED" => true
                ],
                "phone" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("PHONE"))),
                    "LABEL" => "Телефон",
                    "REQUIRED" => true
                ],
                "email" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("EMAIL"))),
                    "LABEL" => "E-mail",
                    "REQUIRED" => true,
                    "EMAIL" => true
                ],
                "timework" => [
                    "VALUE" => nl2br(trim(strip_tags(Request::http_request()->getPost("TIMEWORK")))),
                    "LABEL" => "Время работы",
                    "REQUIRED" => true,
                ],
                "delivery" => [
                    "VALUE" => Request::http_request()->getPost("DELIVERY"),
                    "LABEL" => "Варианты доставки",
                    "LIST" => true,
                    "REQUIRED" => true,
                ],
                "coords" => [
                    "VALUE" => trim(strip_tags(Request::http_request()->getPost("COORDS"))),
                    "LABEL" => "Место положения (координаты)",
                    "REQUIRED" => true,
                    "COORDS" => true
                ],
            ]
        ];

        $arLoadProductArray = array(
            "MODIFIED_BY" => $user_id,
            "IBLOCK_SECTION" => false,
            "NAME" => $request_data['FIELDS']['name']['VALUE'],
            "DETAIL_PICTURE" => $request_data['FIELDS']['image']['VALUE']
        );

        foreach ($request_data as $arr) {
            foreach ($arr as $item) {
                $error = $this->is_valid($item);
                if ($error) {
                    $errors[] = $error;
                }
            }
        }

        if (!empty($errors)) {
            $errors_list = "<ul class='list-unstyled mb-0'>";

            foreach ($errors as $error) {
                $errors_list .= "<li>$error</li>";
            }

            $errors_list .= "</li>";

            Request::json_response(
                'fail',
                $errors_list
            );

        } else {
            $res = $shop_el->Update($shop_id, $arLoadProductArray);

            CIBlockElement::SetPropertyValuesEx($shop_id, Config::$shop_ib_id, [
                'CITY' => $request_data['PROPS']['city']['VALUE'],
                'ADDRESS_TEXT' => $request_data['PROPS']['address']['VALUE'],
                'PHONE' => $request_data['PROPS']['phone']['VALUE'],
                'EMAIL' => $request_data['PROPS']['email']['VALUE'],
                'TIMEWORK' => $request_data['PROPS']['timework']['VALUE'],
                'DELIVERY' => $request_data['PROPS']['delivery']['VALUE'],
                'COORDS' => $request_data['PROPS']['coords']['VALUE'],
            ]);

            Request::json_response('ok', 'Данные профиля успешно обновлены');
        }
    }

    /**
     * @param $field
     * @return bool|string
     */
    function is_valid($field)
    {
        $label = "<b>{$field['LABEL']}</b>";

        if ($field['REQUIRED']) {
            if ($field['VALUE'] == '') {
                return "Поле «{$label}» не должно быть пустым";
            }
        }

        if ($field['EMAIL']) {
            if (!preg_match($this->regex['email'], $field['VALUE'])) {
                return "Введён некорректный e-mail в поле «{$label}»";
            }
        }

        if ($field['COORDS']) {
            if (!preg_match($this->regex['coords'], $field['VALUE'])) {
                return "Введены некорректные координаты в поле «{$label}»";
            }
        }

        if ($field['VALUE']['name'] != '' && $field['FILE'] != false) {
            $exts = $field['FILE']['EXTS'];
            $size = $field['FILE']['SIZE'];

            if ($exts) {
                $file_ext = $this->get_file_extension($field['VALUE']["name"]);

                if (!in_array($file_ext, $exts)) {
                    return "Недопустимый формат файла в поле «{$label}». Текущий формат - <b>$file_ext</b>. Требуется - <b>" . implode(" | ", $field['FILE']['EXTS']) . "</b>";
                }
            }

            if ($size) {
                $current_file_size = $field['VALUE']["size"];

                if ($current_file_size > $size) {
                    return "Превышен допустимый размер файла в поле «{$label}». Текущий размер - {$this->formatSize($current_file_size)}. Требуется не больше - " . $this->formatSize($size) . ".";
                }
            }
        }

        return false;

    }

    /**
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    public function formatSize($bytes, int $decimals = 0): string
    {

        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, $decimals) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, $decimals) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, $decimals) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' байты';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' байт';
        } else {
            $bytes = '0 байтов';
        }

        return $bytes;
    }
}