<?php

namespace app\components;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BrasilApiComponent
{
    private static string $base = 'https://brasilapi.com.br';

    public static function cep(string $cep)
    {
        try {
            $client = new Client();
            $response = $client->get(self::$base . '/api/cep/v1/' . $cep);
    
            if ($response->getStatusCode() !== 200) return false;
    
            $response = json_decode($response->getBody(), true);
    
            if (! isset($response['cep']) or ! $response['cep']) return false;
        } 
        catch (RequestestException $e) {
            return false;
        } 
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    public static function isbn(string $isbn)
    {
        try {
            $client = new Client();
            $response = $client->get(self::$base . '/api/isbn/v1/' . $isbn);
    
            if ($response->getStatusCode() !== 200) return false;
    
            $response = json_decode($response->getBody(), true);
    
            if (! isset($response['isbn']) or ! $response['isbn']) return false;
        } 
        catch (RequestestException $e) {
            return false;
        } 
        catch (Exception $e) {
            return false;
        }

        return true;
    }
}