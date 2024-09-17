<?php

namespace app\controllers;

use app\components\TokenComponent as Token;
use app\controllers\ApiController;

class TokenController extends ApiController
{
    public $modelClass = 'app\models\User';

    public function actionCreate()
    {
        return (new Token($this->modelClass))->create();
    }

    public function actionUpdate()
    {
        return (new Token($this->modelClass))->update();
    }
}