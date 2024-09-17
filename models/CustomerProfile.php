<?php

namespace app\models;

use yii\db\ActiveRecord;

class CustomerProfile extends ActiveRecord
{
    public static function tableName()
    {
        return 'customer_profiles';
    }

    public function rules()
    {
        return [
            [
                ['customer_id', 'profile'],
                'required'
            ]
        ];
    }
}
