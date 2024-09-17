<?php

namespace app\controllers;

use app\components\BookCoverComponent;
use app\controllers\ApiController;

class BookCoverController extends ApiController
{
    public $modelClass = 'app\models\BookCover';
    
    public function actionCreate()
    {
        return (new BookCoverComponent($this->modelClass))->create();
    }
}