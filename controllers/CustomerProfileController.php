<?php

namespace app\controllers;

use app\components\CustomerProfileComponent;
use app\controllers\ApiController;

class CustomerProfileController extends ApiController
{
    public $modelClass = 'app\models\CustomerProfile';
    
    public function actionCreate()
    {
        return (new CustomerProfileComponent($this->modelClass))->create();
    }
}