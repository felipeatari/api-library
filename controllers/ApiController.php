<?php

namespace app\controllers;

use yii\rest\ActiveController;

abstract class ApiController extends ActiveController
{  
    public function actions()
    {
        $actions = parent::actions();

        unset(
            $actions['index'], 
            $actions['view'], 
            $actions['create'], 
            $actions['update'],
            $actions['delete'],
            $actions['options']
        );

        // $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }
}