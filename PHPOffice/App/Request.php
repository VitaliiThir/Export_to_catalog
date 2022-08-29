<?php

namespace PHPOffice\App;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;

class Request
{
    /**
     * @return bool
     */
    public static function is_ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @param string $status
     * @param string $text
     * @param string $file_path
     * @param array $file
     * @param string $table
     * @param string $moderate_file_path
     * @return void
     */
    public static function json_response(
        string $status,
        string $text,
        string $file_path = '',
        array  $file = array(),
        string $table = '',
        string $moderate_file_path = ''
    )
    {
        header('Content-Type: application/json');

        echo json_encode(array(
            'STATUS' => $status,
            'STATUS_TEXT' => $text,
            'FILE_PATH' => $file_path,
            'FILE' => $file,
            'TABLE' => $table,
            'MODERATE_FILE_PATH' => $moderate_file_path
        ));

        die();
    }

    /**
     * @return HttpRequest|\Bitrix\Main\Request
     */
    public static function http_request()
    {
        return Application::getInstance()->getContext()->getRequest();
    }
}