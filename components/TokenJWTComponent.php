<?php

namespace app\components;

use app\components\ResponseComponent as Response;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;

/**
 * Gera e atualiza o Token JWT
 */
trait TokenJWTComponent
{ 
    /**
     * Guarda a chave secreta para gerar o Token JWT
     * 
     * Obs.: Para fins de teste utilizei 'my_secret_key'
     *
     * @var string
     */
    private static string $secret_key = SECRET_KEY;

    /**
     * Tempo de expiração do token
     *
     * @var integer
     */
    private static int $expire_token = EXPIRE_TOKEN;
    
    /**
     * Tempo de expiração do refresh token
     *
     * @var integer
     */
    private static int $expire_refresh_token = EXPIRE_REFRESH_TOKEN;

    /**
     * Gera o Token JWT
     *
     * @param array $user Dados do Usuário
     * 
     * @return array
     */
    public static function generator(array $user): array
    {
        $time = time(); // Garante trabalhar com o mesmo timestamp
        $expire_token = $time + self::$expire_token;
        $expire_refresh_token = $time + self::$expire_refresh_token; // Importante o expire token ser maior que o token
 
        $payload = [
            'iat' => $time,
            'exp' => $expire_token,
            'data' => [
                'id' => $user['id'],
                'type' => ''
            ]
        ];

        $payload['data']['type'] = 'token';

        $token = $payload;

        $payload['exp'] = $expire_refresh_token;
        $payload['data']['type'] = 'refresh_token';

        $refresh_token = $payload;
        
        return [
            'token' => JWT::encode($token, self::$secret_key, 'HS256'),
            'refresh_token' => JWT::encode($refresh_token, self::$secret_key, 'HS256'),
            'expire_token' => $expire_token,
            'expire_refresh_token' => $expire_refresh_token,
        ];
    }

    /**
     * Atualiza o Token JWT
     *
     * @param array $user Dados do Usuário
     * 
     * @return array
     */
    public static function refresh(array $user): array
    {
        try {
            $jwtDecode = JWT::decode($user['refresh_token'], new Key(self::$secret_key, 'HS256'));

            if (! isset($jwtDecode->data->type) or $jwtDecode->data->type !== 'refresh_token') {
                throw new Exception('Token Inválido!', 401);
            }

            if ((int) $jwtDecode->data->id !== (int) $user['id']) {
                throw new Exception('ID do usuário não conrresponde com o refresh_token.', 403);
            }
        } 
        catch (Firebase\JWT\ExpiredException $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        } 
        catch (Exception $e) {
            return [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

        return self::generator($user);
    }

    /**
     * Verifica se o token é válido ou ainda não expirou
     *
     * @return Response|void
     */
    public static function verify()
    {
        $token ??= Yii::$app->request->headers['authorization'];

        if (! $token) {
            return Response::api(400, 'Token não informado', status: false);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $jwtDecode = JWT::decode($token, new Key(self::$secret_key, 'HS256'));

            if (! $jwtDecode) {
                return Response::api(400, 'Token inválido: Verifique se foi informado.', status: false);
            }

            if ($jwtDecode->data->type === 'refresh_token') {
                return Response::api(400, 'Token inválido: Está tentando acessar com o refresh_token.', status: false);
            }
        } 
        catch (\Firebase\JWT\ExpiredException $e) {
            return Response::api(401, $e->getMessage(), status: false);
        } 
        catch (\Exception $e) {
            return Response::api(401, $e->getMessage(), status: false);
        }
    }
}