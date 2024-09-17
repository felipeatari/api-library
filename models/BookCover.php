<?php

namespace app\models;

use yii\db\ActiveRecord;

class BookCover extends ActiveRecord
{
    public static function tableName()
    {
        return 'book_covers';
    }

    public function rules()
    {
        return [
            [
                ['book_id', 'cover'],
                'required'
            ]
        ];
    }
}
