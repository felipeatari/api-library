<?php

return [
    'class' => 'yii\db\Connection',
    // 'dsn' => 'mysql:host=localhost:3306;dbname=yii2', // App
    'dsn' => 'mysql:host=api_library_db;dbname=yii2', // Docker
    'username' => 'yii2',
    'password' => 'yii2',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
