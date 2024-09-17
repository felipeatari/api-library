<?php

return [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'token',
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'customer',
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'book',
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'customer-profile',
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'book-cover',
        ],
    ],
];