<?php

namespace app\controllers;

use app\components\ResponseComponent as Response;
use Yii;

class ErrorController
{
    /**
     * Captura os erros 404 e 405
     */
    public function __construct()
    {
        $url = Yii::$app->request->getUrl();
        $method = Yii::$app->request->getMethod();
        $httpResponseCode = 405;

        $code = 405;
        $message = 'Method not Implemented';
        
        if ($url === '/' and $method !== 'GET') {
            $httpResponseCode = 405;
            $message = 'Method not Implemented';
        } elseif ($url === '/' and $method === 'GET') {
            $httpResponseCode = 200;
        } else {
            $rules = Yii::$app->urlManager->rules;

            $arr_rules = [];
    
            foreach ($rules as $rule):
                foreach ($rule as $ruleURLs):
                    foreach ($ruleURLs as $ruleURL):
                        if (substr($url, -1) !== 's') continue;
    
                        if ($ruleURL !== rtrim(trim($url, '/'), 's')) continue;
    
                        $arr_rules[] = true;
                    endforeach;
                endforeach;
            endforeach;
    
            if (! $arr_rules) {
                $httpResponseCode = 404;
                $message = 'Not Found';
            }
        }

        if ($httpResponseCode !== 200) {

            return Response::api($httpResponseCode, $message, status: false);
        }

        $data = [
            'message' => 'Hello at my API Library!',
            'author' => 'Dev Luiz Felipe',
        ];

        return Response::api($httpResponseCode, data: $data);
    }
}