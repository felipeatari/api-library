<?php

namespace app\controllers;

use app\controllers\ApiController;
use app\components\CustomerComponent;

class CustomerController extends ApiController
{
    public $modelClass = 'app\models\Customer';

    public function actionIndex()
    {   
        return (new CustomerComponent($this->modelClass))->index();
    }
    
    public function actionCreate()
    {
        return (new CustomerComponent($this->modelClass))->create();
    }
}