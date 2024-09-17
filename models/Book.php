<?php

namespace app\models;

use yii\db\ActiveRecord;

class Book extends ActiveRecord
{
    public static function tableName()
    {
        return 'books';
    }

    public function rules()
    {
        return [
            [
                ['isbn', 'titulo', 'autor', 'preco', 'estoque'],
                'required'
            ]
        ];
    }
}
