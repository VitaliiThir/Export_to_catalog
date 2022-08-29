<?php

namespace PHPOffice\App;

class Config
{

    /**
     * @var int
     */
    public static int $shop_ib_id = 4;

    /**
     * @var int
     */
    public static int $catalog_ib_id = 2;

    /**
     * @var int
     */
    public static int $offers_ib_id = 3;

    /**
     * @var string
     */
    public static string $shops_root_dir = 'export';

    /**
     * @var int
     */
    public static int $table_column_index_required = 2;

    /**
     * @var array|string[]
     */
    public static array $required_extensions = ['xls', 'xlsx'];


    /**
     * @var array|string[][]
     */
    public static array $tabs_arr = [
        "profile" => [
            "name" => "Профиль магазина",
            "query" => "profile"
        ],
        "save" => [
            "name" => "Загрузить таблицу",
            "query" => "save"
        ],
        "export" => [
            "name" => "Экспорт в каталог",
            "query" => "export"
        ],
        "moderation" => [
            "name" => "Товары на модерации",
            "query" => "moderation"
        ],
        "catalog" => [
            "name" => "Каталог товаров",
            "query" => "catalog"
        ],
    ];

}