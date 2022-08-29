<?php

namespace PHPOffice\App;

class Util
{
    /**
     * @param $arr
     * @return void
     */
    public static function printR($arr): void
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    /**
     * @param $dir
     * @return void
     */
    public static function create_directory($dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }

    /**
     * @param $dir
     * @return void
     */
    public static function clear_files($dir): void
    {
        if (file_exists($dir)) {
            foreach (glob("$dir/*") as $file) {
                unlink($file);
            }
        }
    }

    /**
     * @param $num
     * @param $titles
     * @return mixed
     */
    public static function ending_word($num, $titles): mixed
    {
        $cases = array(2, 0, 1, 1, 1, 2);

        // echo declOfNum(5, array('человек просит', 'человека просят', 'человек просят'));
        return $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];

    }

    /**
     * @param $str
     * @return string
     */
    public static function test($str): string
    {
        return preg_match('/[\D]/', $str);
    }

    /**
     * @return void
     */
    public static function loader(): void
    {
        ?>
        <div class="preloader">
            <div class="preloader-items">
                <div class="loader">
                    <img src="/<?= Config::$shops_root_dir ?>/assets/images/loader.gif" alt="">
                </div>
                <div class="text"><span class="text-status">Загрузка</span> ...</div>
            </div>
        </div>
        <?
    }

    /**
     * @param $array
     * @param $key
     * @param bool $double_search
     * @return array
     */
    public static function array_unique_key($array, $key, bool $double_search = false): array
    {
        $tmp = $key_array = array();
        $double_tmp = $double_key_array = array();
        $i = 0;

        if (!$double_search) {
            foreach ($array as $val) {
                if (!in_array($val[$key], $key_array)) {
                    $key_array[$i] = $val[$key];
                    $tmp[$i] = $val;
                }
                $i++;
            }
        } else {
            foreach ($array as $val) {
                if (!in_array($val[$key], $key_array)) {
                    $key_array[$i] = $val[$key];
                    $tmp[$i] = $val;
                } else {
                    $double_key_array[$i] = $val[$key];
                    $double_tmp[$i] = $val;
                }
                $i++;
            }
        }

        return !$double_search ? $tmp : $double_tmp;
    }

    /**
     * @param $str
     * @return array|string|null
     */
    public static function get_replace_phone($str)
    {
        return preg_replace('~[^0-9]+~', '', $str);
    }


}