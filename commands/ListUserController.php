<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

class ListUserController extends Controller
{
    public function actionIndex()
    {
        $user = new User();

        $users = $user->find()->all();

        if (! $users) {
            echo 'Usuário(s) não encontrado(s).';

            echo "\n";

            return ExitCode::OK;
        }

        $users = array_map(fn($user) => $user->toArray(), $users);

        print_r($users);

        echo "\n";

        return ExitCode::OK;
    }
}
