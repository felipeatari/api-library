<?php

namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    public static function tableName()
    {
        return 'customers';
    }

    public function rules()
    {
        return [
            [
                ['nome', 'cpf', 'cep', 'logradouro', 'cidade', 'estado'],
                'required'
            ]
        ];
    }
}
