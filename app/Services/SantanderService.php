<?php

namespace App\Services;

use App\Models\{AccessToken, BankAccount};
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class SantanderService
{
    protected Client $client;

    protected string $base_uri;

    protected string $base_uri_oauth;

    protected BankAccount $bankAccount;

    protected string $cert;

    protected string $key;

    protected string $client_id;

    protected string $client_secret;

    // Método construtor do client.
    // Ao instanciar, deve enviar certificado e também chave privada.

    public function __construct()
    {
        if (env('ENVIRONMENT_API') === 'sandbox') {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI_SANDBOX'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI_SANDBOX') . '/bank_account_information/v1'; // Base URI para requisições de client.
        } else {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI') . '/bank_account_information/v1'; // Base URI para requisições de client.
        }

        $this->cert = base_path(env('API_CERT_PATH')); //Client ID para autenticação de client.
        $this->key  = base_path(env('API_KEY_PATH')); // ClientSecret para autenticação de cliente.

        $this->client = new Client([
            'cert'    => $this->cert, // Anexa o certificado.
            'ssl_key' => $this->key, // Anexa a chave privada.
        ]);
    }

    // Método define propriedades da classe, a partir BankAccount recebido como argumento.
    private function setBankAccount(BankAccount $bankAccount): void
    {
        $this->bankAccount   = $bankAccount;
        $this->cert          = $bankAccount->certificate_path;
        $this->key           = $bankAccount->key_path;
        $this->client_id     = Crypt::decryptString($bankAccount->client_id);
        $this->client_secret = Crypt::decryptString($bankAccount->client_secret);
    }

    // Método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    private function generateAccessToken(): string
    {
        try {
            $response = $this->client->post($this->base_uri_oauth . '/auth/oauth/v2/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    // 'client_id'     => $this->client_id,
                    // 'client_secret' => $this->client_secret,
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
    private function getValidAccessToken(): string
    {
        // Busca último token emitido.
        $data = AccessToken::query()
            ->where('bank_account_id', '=', $this->bankAccount->id)
            ->where('environment', '=', env('ENVIRONMENT_API'))
            ->orderBy('id', 'DESC')->first();

        // Se inválido, requisita novo
        if ($data) {
            // Invoca método para testar o token.
            if ($data->token && $this->isTokenValid($data->expires_at)) {
                // Retorna o último token registrado, e que ainda não expirou.
                return $data->token;
            }
        }

        // Como não encontrado token registrado válido, gera um novo.
        return $this->generateAccessToken();
    }

    // Método confere validade do Access Token.
    // Recebe como parâmetro quando expira o token já registrado.
    private function isTokenValid(string $expire): bool
    {
        // Obtêm a data atual.
        $now = Carbon::now();

        // Verifica se a data de expiração é maior ou igual à data de hoje.
        if ($expire >= $now) {
            // Data de expiração maior que atual
            return true;
        } else {
            // Data de expiração é menor que atual.
            return false;
        }
    }

    // Método calcula quando irá expirar o token.
    private function expireToken(): DateTime
    {
        $now    = Carbon::now();
        $expire = $now->addSeconds(900);

        return $expire;
    }

    // Método para armazenar o token de acesso criado no BD.
    private function storeAccessToken(mixed $token): void
    {
        // Salva o token na base de dados.
        AccessToken::create([
            'bank_account_id'   => $this->bankAccount->id,
            'environment'       => env('ENVIRONMENT_API'), // Ambiente que gerou o token.
            'token'             => $token['access_token'],
            'expires_in'        => $token['expires_in'],
            'expires_at'        => $this->expireToken(),
            'not_before_policy' => $token['not-before-policy'],
            'session_state'     => $token['session_state'],
            'status'            => true,
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
            // Método para setar propriedades da classe.
            $this->setBankAccount($bankAccount);

            // Obtém um token válido.
            $token = $this->getValidAccessToken();

            // Prepara os parâmetros da requisição.
            $params = "?initialDate=$initial_date&finalDate=$final_date&_offset=1&_limit=50";
            // Prepara o statements com 17 caracteres, concatenando agência e conta com o dígito -> 0000.000000000000
            $statements = $bankAccount->account_agency . '.' . Str::padLeft($bankAccount->account_number, 12, '0');

            // Faz a requisição com o Token válido e ClientId.
            $response = $this->client->get($this->base_uri . "/banks/90400888000142/statements/$statements" . $params, [
                'headers' => [
                    'X-Application-Key' => Crypt::decryptString($this->bankAccount->client_id),
                    'Authorization'     => "Bearer {$token}",
                ],
            ]);

            // Recupera na variável os dados da resposta da API, decodificando.
            $dados = json_decode($response->getBody(), true);
            //dd($dados);

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
                'date'            => $this->formatDate($transaction['transactionDate']),
                'bank_account_id' => $bankAccount->id,
                //'complement' => $transaction['historicComplement'],
                //'bank' => $bankAccount->bank->bank_name,
            ];
        })->toArray();
    }

    // Método para formatar a data de uma string para o padrão 'Y-m-d'.
    protected function formatDate(string $date): string
    {
        // Converte a data para o padrão 'Y-m-d', caso seja no formato 'd/m/Y'.
        if (Carbon::hasFormat($date, 'd/m/Y')) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }

        // Retorna sem formatar.
        return $date;
    }
}
