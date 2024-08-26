<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SantanderService
{
    protected mixed $client;

    protected string $base_uri;

    protected string $base_uri_oauth;

    // Método construtor do client.
    // Ao instanciar, deve enviar certificado e também chave privada.

    public function __construct()
    {
        $this->base_uri_oauth = env('SANTANDER_BASE_URI_OAUTH'); // Base URI para autenticação de client.
        $this->base_uri       = env('SANTANDER_BASE_URI'); // Base URI para requisição de client.

        $this->client = new Client([
            'cert'    => base_path(env('API_CERT_PATH')), // Anexa o certificado.
            'ssl_key' => base_path(env('API_KEY_PATH')), // Anexa a chave privada.
        ]);
    }

    // Este método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    public function getAccessToken(): mixed
    {
        try {
            $response = $this->client->post($this->base_uri_oauth . '/auth/oauth/v2/token', [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => env('SANTANDER_CLIENT_ID'),
                    'client_secret' => env('SANTANDER_CLIENT_SECRET'),
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['access_token'];
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Failed to obtain access token', 'message' => $e->getMessage()], 403);
        }
    }
}
