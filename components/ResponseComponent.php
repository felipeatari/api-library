<?php

namespace app\components;

use Yii;

class ResponseComponent
{
    /**
     * Retorno da requisição
     *
     * @param integer $status_code
     * @param string $message
     * @param array $data
     * @param bool $status
     * 
     * @return void
     */
    public static function api(int $status_code = 200, string $message = '', array|object $data = [], $status = true): void
    {
        $content = ['code' => $status_code];
 
        if ($status) $content['status'] = 'success';
        else $content['status'] = 'error';

        if ($message) $content['message'] = $message;

        if ($data) $content['data'] = $data;

        header('Content-Type: application/json');
        header('Accept: application/json');

        http_response_code($status_code);

        die(json_encode($content));
    }
}