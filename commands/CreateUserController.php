<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class CreateUserController extends Controller
{
    public function actionIndex()
    {
        $nome = 'Admin';
        $senha = '12345';

        $senha = Yii::$app->getSecurity()->generatePasswordHash($senha);

        $user = new User();

        $find = $user->findOne(['nome' => $nome]);

        if ($find) {
            echo 'O usuário já foi cadastrado.';

            echo "\n";

            return ExitCode::OK;
        }

        $user->nome = $nome;
        $user->senha = $senha;

        if (! $user->save()) {
            echo 'Falha ao cadastrar usuário';
        }
        else {
            echo 'Usuário cadastrado com sucesso';
        }

        echo "\n";

        return ExitCode::OK;
    }
}
