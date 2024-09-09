<?php

namespace App\Services;

use App\Models\{BankAccount, SantanderToken};
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class SantanderService
{
    protected mixed $client;

    protected string $base_uri;

    protected string $base_uri_oauth;

    protected string $client_secret;

    protected string $client_id;

    // Método construtor do client.
    // Ao instanciar, deve enviar certificado e também chave privada.

    public function __construct()
    {
        if (env('SANTANDER_AMBIENTE') === 'sandbox') {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI_SANDBOX'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI_SANDBOX') . '/bank_account_information/v1'; // Base URI para requisições de client.
            $this->client_id      = env('SANTANDER_CLIENT_ID_SANDBOX'); // Client ID para autenticação de client.
            $this->client_secret  = env('SANTANDER_CLIENT_SECRET_SANDBOX'); // ClientSecret para autenticação de cliente.
        } else {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI') . '/bank_account_information/v1'; // Base URI para requisições de client.
            $this->client_id      = env('SANTANDER_CLIENT_ID'); // Client ID para autenticação de client.
            $this->client_secret  = env('SANTANDER_CLIENT_SECRET'); // ClientSecret para autenticação de cliente.
        }

        $this->client = new Client([
            'cert'    => base_path(env('API_CERT_PATH')), // Anexa o certificado.
            'ssl_key' => base_path(env('API_KEY_PATH')), // Anexa a chave privada.
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
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Armazena o token de acesso e sua data de expiração.
            $this->storeAccessToken($data);

            return $data['access_token'];
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            dd('Erro ao solicitar no endpoint token:', $e);
        }
    }

    // Método para buscar e validar último token.
    // Se inválido, requisita novo.
    public function getValidAccessToken(): string
    {
        // Busca último token emitido.
        $data = SantanderToken::query()
            ->where('type_token', '=', env('SANTANDER_AMBIENTE'))
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
            return true;
        } else {
            // Data de expiração é menor que atual.
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

    // Método para armazenar o token de acesso criado no BD.
    private function storeAccessToken(mixed $token): void
    {
        SantanderToken::create([
            'type_token'        => env('SANTANDER_AMBIENTE'), // Especifica qual foi o ambiente que gerou o token.
            'access_token'      => $token['access_token'],
            'expires_in'        => $token['expires_in'],
            'expires_at'        => $this->expireToken(),
            'not_before_policy' => $token['not-before-policy'],
            'session_state'     => $token['session_state'],
        ]);
    }

    // Consulta de Saldo. Deve enviar no header um Token válido e o ClientId.
    // Endpoint: GET -> '/banks/banks/{bank_id}/balances/{balance_id}'
    public function getAccountSaldo(): mixed
    {
        try {
            // Obtém um token válido.
            $token = $this->getValidAccessToken();

            // Faz a requisição com o Token e ClientId.
            $response = $this->client->get($this->base_uri . '/banks/90400888000142/balances/2194.000130010584', [
                'headers' => [
                    'X-Application-Key' => $this->client_id,
                    'Authorization'     => "Bearer {$token}",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Retorna a mensagem de erro.
            dd('Erro ao submeter requisição saldo:', $e);
        }
    }

    // Listagem de Extrato. Deve enviar no header um Token válido e o ClientId.
    // Endpoint: GET -> '/banks/{bank_id}/statements/{statement_id}?initialDate=2022-10-01&finalDate=2022-10-30&_offset=1&_limit=50'
    public function getAccountExtrato(string $initial_date, string $finalDate, int $page): mixed
    {
        try {
            // Obtém um token válido.
            $token = $this->getValidAccessToken();
            // Parâmetros da requisição.
            $params = "?initialDate=$initial_date&finalDate=$finalDate&_offset=$page&_limit=50";

            // Faz a requisição com o Token e ClientId.
            $response = $this->client->get($this->base_uri . '/banks/90400888000142/statements/2194.000130010584' . $params, [
                'headers' => [
                    'X-Application-Key' => $this->client_id,
                    'Authorization'     => "Bearer {$token}",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Retorna a mensagem de erro.
            dd('Erro ao submeter requisição saldo:', $e);
        }
    }

    // Listagem de Contas. Deve enviar no header um Token válido e o ClientId.
    // Endpoint: GET -> '/banks/{bank_id}/accounts?_offset={number}&_limit={number}'
    public function getAccountsList(int $page): mixed
    {
        try {
            // Obtém um token válido.
            $token = $this->getValidAccessToken();
            // Parâmetros da requisição.
            $params = "_offset=$page&_limit=10";

            // Faz a requisição com o Token e ClientId.
            $response = $this->client->get($this->base_uri . '/banks/90400888000142/accounts?' . $params, [
                'headers' => [
                    'X-Application-Key' => $this->client_id,
                    'Authorization'     => "Bearer {$token}",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Retorna a mensagem de erro.
            dd('Erro ao submeter requisição saldo:', $e);
        }
    }

    // Método faz requisição do extrato a API Saldo e Extrato do Santander.
    // Passar no header da requisição a credencial: na chave X-Application-Key o clientId; e na Authorization um token válido.
    // PARÂMETRO: período (data inicial e data final).
    public function fetchTransactions(BankAccount $bankAccount, string $initial_date, string $final_date): mixed
    {
        try {
            // Obtém um token válido.
            $token = $this->getValidAccessToken();

            // Prepara os parâmetros da requisição.
            $params = "?initialDate=$initial_date&finalDate=$final_date&_offset=1&_limit=50";
            // Prepara o statements com 17 caracteres, concatenando agência e conta com o dígito -> 0000.000000000000
            $statements = $bankAccount->account_agency . '.' . Str::padLeft($bankAccount->account_number, 12, '0');

            // Faz a requisição com o Token válido e ClientId.
            $response = $this->client->get($this->base_uri . "/banks/90400888000142/statements/$statements" . $params, [
                'headers' => [
                    'X-Application-Key' => $this->client_id,
                    'Authorization'     => "Bearer {$token}",
                ],
            ]);

            // Recupera na variável os dados da resposta da API, decodificando.
            $dados = json_decode($response->getBody(), true);

            // Verifica se retornou transações na chave '_content'.
            if (array_key_exists('_content', $dados)) {
                // Para retornar, formata as transações obtidas, conforme padrão.
                return $this->formatTransactions($dados, $bankAccount);
            } else {
                return [];
            }
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()]);
        }
    }

    // Método formata as transações obtidas para padrão comum a todos os bancos.
    protected function formatTransactions(mixed $transactions, BankAccount $bankAccount): mixed
    {
        return collect($transactions['_content'])->map(function ($transaction) use ($bankAccount) {
            return [
                'type'            => $transaction['creditDebitType'] == 'DEBITO' ? 'debit' : 'credit',
                'description'     => $transaction['transactionName'],
                'amount'          => $transaction['amount'],
                'date'            => Carbon::createFromFormat('d/m/Y', $transaction['transactionDate'])->format('Y-m-d'),
                'bank_account_id' => $bankAccount->id,
                //'complement' => $transaction['historicComplement'],
                //'bank' => $bankAccount->bank->bank_name,
            ];
        })->toArray();
    }
}
