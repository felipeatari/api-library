<?php

namespace app\controllers;

use app\controllers\ApiController;
use app\components\BookComponent;

class BookController extends ApiController
{
    public $modelClass = 'app\models\Book';

    public function actionIndex()
    {   
        return (new BookComponent($this->modelClass))->index();
    }
    
    public function actionCreate()
    {
        return (new BookComponent($this->modelClass))->create();
    }
}