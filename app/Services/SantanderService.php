<?php

namespace App\Services;

use App\Models\SantanderToken;
use Carbon\Carbon;
use DateTime;
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

    // Método para buscar e validar último token.
    // Se inválido, requisita novo.
    public function getValidAccessToken(): string
    {
        // Busca último token emitido.
        $data = SantanderToken::query()
            ->orderBy('id', 'DESC')->first();

        if ($data) {
            // Invoca método para testar o token.
            if ($data->access_token && $this->isTokenValid($data->expires_at)) {

                // Retorna o último token registrado, e que ainda não expirou.
                return $data->access_token;
            }
        }

        // Como não encontrado token registrado válido, gera um novo.
        return $this->generateAccessToken();
    }

    // Método confere validade do Access Token.
    // Recebe como parâmetro quando expira o token já registrado.
    public function isTokenValid(DateTime $expire): bool
    {
        $now = Carbon::now();

        if ($expire >= $now) {
            // Data de expiração maior que atual
            //dump('Agora: ' . $now . '// Expira: ' . $expire);
            return true;
        } else {
            // Data de expiração é menor que atual.
            //dump('menor', $expire);
            return false;
        }
    }

    // Método calcula quando irá expirar o token.
    public function expireToken(): DateTime
    {
        $now    = Carbon::now();
        $expire = $now->addSeconds(900);

        return $expire;
    }

    // Implemente seu método para armazenar o token de acesso e sua data de expiração
    // No seu banco de dados, por exemplo.
    private function storeAccessToken(mixed $token): void
    {
        SantanderToken::create([
            'access_token'      => $token['access_token'],
            'expires_in'        => $token['expires_in'],
            'expires_at'        => $this->expireToken(),
            'not_before_policy' => $token['not-before-policy'],
            'session_state'     => $token['session_state'],
        ]);
    }

    // Método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    public function generateAccessToken(): string
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

            // Armazena o token de acesso e sua data de expiração.
            $this->storeAccessToken($data);

            // Coloca na propriedade da classe, o access_token e o expires_in.
            //$this->accessToken = $data['access_token'];
            //$this->expiresToken = $this->expireToken();

            dump($data);

            return $data['access_token'];
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            //return response()->json(['error' => 'Failed to obtain access token', 'message' => $e->getMessage()], 403);
            dd('Erro ao solicitar no endpoint token:', $e);
        }
    }

    // Busca Saldo.
    public function getAccountSaldo(): mixed
    {
        try {
            // Obtém um token válido.
            $token = $this->getValidAccessToken();

            // Realiza a requisição com o token.
            //$response = $this->client->get($this->base_uri . "/banks/90400888081550/balances/2194.000130010584", [
            //$response = $this->client->get($this->base_uri . '/banks/90400888000142/balances/0000.000011112222', [
            $response = $this->client->get($this->base_uri . '/banks/90400888081550/balances/2194.000130010584', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            dump($response);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Retorna a mensagem de erro.
            dd('Erro ao submeter requisição endpoint saldo:', $e);
        }
    }

    // Este método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    public function getAccessToken(): string
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
