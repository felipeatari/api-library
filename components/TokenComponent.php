<?php

namespace app\components;

use App\Models\User;
use app\components\TokenJWTComponent as Token;
use app\components\ResponseComponent as Response;
use Yii;

/**
 * Gerencia o Token
 */
class TokenComponent
{
    use Token;

    public function __construct(
        private mixed $user
    )
    {
    }

    public function create()
    {
        $request = Yii::$app->request->post();

        if (! isset($request['nome']) or ! $request['nome']) {
            return Response::api(400, 'Verifique o campo nome.', status: false);
        }

        if (! isset($request['senha']) or ! $request['senha']) {
            return Response::api(400, 'Verifique o campo senha.', status: false);
        }

        $user = $this->user::findOne(['nome' => $request['nome']]);

        if (! $user) {
            return Response::api(404, 'Usuário não encontrado.', status: false);
        }

        $user = $user->toArray();

        if (! $this->validatePassword($request['senha'], $user['senha'])) {
            return Response::api(401, 'Não autorizado.', status: false);
        }

        $data = self::generator($user);

        return Response::api(201, data: $data);
    }

    public function update()
    {
        $request = Yii::$app->request->post();
        $userID = Yii::$app->request->getQueryParam('id');

        if (! isset($request['refresh_token']) or ! $request['refresh_token']) {
            return Response::api(400, 'Verifique o campo refresh_token.', status: false);
        }
        
        $user = $this->user::findOne($userID);

        if (! $user) {
            return Response::api(404, 'Usuário não encontrado.', status: false);
        }

        $request['id'] = $user->id;

        $data = self::refresh($request);

        if (! isset($data['token']) or ! $data['token']) {
            return Response::api(400, $data['message']);
        }

        return Response::api(201, data: $data);
    }

    /**
     * Gera a senha do usuário de forma segura para ser salva
     *
     * @param string $senha Sem encripitação
     * 
     * @return string Senha encripitada
     */
    private function securePassword(string $senha): string
    {
        return Yii::$app->getSecurity()->generatePasswordHash($senha);
    }

    /**
     * Verifica se o password possí a has criada anteriormente
     *
     * @param string $senha Senha informada pelo usuário
     * @param string $hash Senha encriptada
     * 
     * @return boolean
     */
    private function validatePassword(string $senha, string $hash): bool
    {
        return Yii::$app->getSecurity()->validatePassword($senha, $hash);
    }
}