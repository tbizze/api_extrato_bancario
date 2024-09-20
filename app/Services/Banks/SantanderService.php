<?php

namespace App\Services\Banks;

use App\Models\{AccessToken, BankAccount};
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\{Crypt, Http, Log};
use Illuminate\Support\Str;

class SantanderService
{
    protected string $base_uri;

    protected string $base_uri_oauth;

    protected BankAccount $bankAccount;

    protected string $certificate;

    protected string $key;

    protected string $client_id;

    protected string $client_secret;

    // Método construtor do client.
    public function __construct()
    {
        if (env('ENVIRONMENT_API') === 'sandbox') {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI_SANDBOX'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI_SANDBOX') . '/bank_account_information/v1'; // Base URI para requisições de client.
        } else {
            $this->base_uri_oauth = env('SANTANDER_BASE_URI'); // Base URI para autenticação de client.
            $this->base_uri       = env('SANTANDER_BASE_URI') . '/bank_account_information/v1'; // Base URI para requisições de client.
        }
    }

    // Método define propriedades da classe, a partir BankAccount recebido como argumento.
    private function setProperties(BankAccount $bankAccount): void
    {
        $this->bankAccount   = $bankAccount;
        $this->certificate   = storage_path(env('API_CERT_PATH') . $bankAccount->certificate_path);
        $this->key           = storage_path(env('API_KEY_PATH') . $bankAccount->key_path);
        $this->client_id     = Crypt::decryptString($bankAccount->client_id);
        $this->client_secret = Crypt::decryptString($bankAccount->client_secret);
    }

    // Método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    private function generateToken(): mixed
    {
        try {
            // Fazer a requisição para obter o token de acesso.
            $response = Http::asForm()->withOptions([
                'cert'    => $this->certificate,
                'ssl_key' => $this->key,
            ])->post($this->base_uri_oauth . '/auth/oauth/v2/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
            ]);

            // Coloca o response da API em variável json.
            $data = $response->json();

            // Quando requisição for bem sucedida, segue lógica para retornar o token.
            if ($response->successful()) {

                // Armazenar os dados do token no BD
                $this->storeAccessToken($data);

                // Retorna o token criado.
                return $data['access_token'];
            }

            // Se requisição não teve sucesso. Trata o erro e retorna informando.
            // Inicializa a variável $error.
            $error = ['error' => 'Erro desconhecido.', 'message' => 'Falha ao obter token.'];

            if ($response->unauthorized()) {
                $error = ['error' => '401 Acesso negado.', 'message' => $data['error_description']];
            }

            if ($response->notFound()) {
                $error = ['error' => '404 Não encontrado.', 'message' => $data['fault']['faultstring']];
            }

            // Registro erro no LOG.
            Log::error('SantanderService: Erro ao gerar token | ' . $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | ' . $error['error'] . ' - ' . $error['message']);

            // Retorna mensagem de erro.
            return $error;
        } catch (\Exception $e) {
            // Registra o erro no LOG.
            Log::error('SantanderService: Erro ao gerar token | ' . $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => ' . $e->getMessage());

            // Retorna a mensagem de erro.
            return ['error' => 'Erro ao gerar token.', 'message' => $e->getMessage()];
        }
    }

    // Método para buscar e validar último token.
    // Se inválido, requisita novo.
    private function getValidToken(): mixed
    {
        // Busca no BD o último token emitido.
        $data = AccessToken::query()
            ->where('bank_account_id', '=', $this->bankAccount->id)
            ->where('environment', '=', env('ENVIRONMENT_API'))
            ->where('status', '=', 1)
            ->orderBy('id', 'DESC')->first();

        // Se houver token no BD. Segue validando.
        if ($data) {
            // Confirma a validade do token.
            if ($data->token && $this->validateToken($data->expires_at)) {
                // Retorna o token armazenado.
                return $data->token;
            }
            // Existe token, mas inválido. Atualiza status do token.
            $data->update(['status' => 0]);
        }

        // Como não foi encontrado token válido, gera um novo.
        return $this->generateToken();
    }

    // Método confere validade do Access Token.
    // Recebe como parâmetro quando expira o token já registrado.
    private function validateToken(string $expire): bool
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
    private function makeExpirationToken(): DateTime
    {
        // Obtém a data atual.
        $now = Carbon::now();
        // Adiciona 15 minutos (900 segundos) ao tempo atual, para gerar a data de expiração.
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
            'expires_at'        => $this->makeExpirationToken(),
            'not_before_policy' => $token['not-before-policy'],
            'session_state'     => $token['session_state'],
            'status'            => true,
        ]);
    }

    /*
    Parâmetros:
      _limit: total máximo de itens por página => mín:1 máx:50
      _offset: de qual página a consulta deve trazer os resultados => padrão:1
     */
    public function fetchAllTransactions(BankAccount $bankAccount, string $initial_date, string $final_date): mixed
    {
        $allTransactions = [];
        $pageNumber      = 1;
        $totalPages      = 1;

        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        // Obtém um token válido.
        $token = $this->getValidToken();

        // Se teve erro ao buscar o token, retorna mensagem.
        if (is_array($token) && array_key_exists('error', $token)) {
            return $token;
        }

        // Prepara o statements com 17 caracteres, concatenando agência e conta com o dígito -> 0000.000000000000.
        $statements = $bankAccount->account_agency . '.' . Str::padLeft($bankAccount->account_number, 12, '0');

        // Faz a requisição para obter as transações.
        $response = Http::withToken($token)->withHeaders([
            'X-Application-Key' => $this->client_id,
        ])
            ->withOptions([
                'cert'    => $this->certificate,
                'ssl_key' => $this->key,
            ])
            ->get($this->base_uri . "/banks/90400888000142/statements/$statements", [
                'initialDate' => $initial_date,
                'finalDate'   => $final_date,
                '_offset'     => $pageNumber,
                '_limit'      => '50',
            ]);

        // Caso requisição não tenha sucesso.
        if ($response->failed()) {
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        }

        // Coloca o response da API em variável json.
        $transactions = $response->json();

        // Checa se obteve transações na chave '_content'.
        // Adiciona transações retornadas à lista de transações.
        if (array_key_exists('_content', $transactions) && count($transactions['_content'])) {
            $allTransactions = array_merge($allTransactions, $transactions['_content']);
        }

        // Checa informações de páginas na chave '_pageable'.
        // Pega o total de páginas.
        if (array_key_exists('_pageable', $transactions) && count($transactions['_pageable'])) {
            $totalPages = $transactions['_pageable']['totalPages'];
        }

        // Caso o número de páginas seja maior que um.
        if ($totalPages > 1) {
            // Executa um Loop até que atinja o número total de páginas.
            for ($page = 2; $page <= $totalPages; $page++) {

                // Faz a requisição para obter as transações da próxima página.
                $response = Http::withToken($token)->withHeaders([
                    'X-Application-Key' => $this->client_id,
                ])
                    ->withOptions([
                        'cert'    => $this->certificate,
                        'ssl_key' => $this->key,
                    ])
                    ->get($this->base_uri . "/banks/90400888000142/statements/$statements", [
                        'initialDate' => $initial_date,
                        'finalDate'   => $final_date,
                        '_offset'     => $page,
                        '_limit'      => '50',
                    ]);

                // Checa se obteve transações na chave '_content'.
                // Adiciona transações retornadas à lista de transações.
                $allTransactions = array_merge($allTransactions, $this->checkResponse($response));
            }
        }

        if (!empty($allTransactions)) {

            return $this->formatTransactions($allTransactions);
        } else {

            // Retorna mensagem informando que não obteve transações.
            return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
        }
    }

    // Método faz requisição do extrato a API Saldo e Extrato do Santander.
    // Submeter o token. Passar no header o clientId através da chave X-Application-Key.
    // PARÂMETRO: período (data inicial e data final).
    public function fetchTransactions(BankAccount $bankAccount, string $initial_date, string $final_date): mixed
    {
        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        try {
            // Obtém um token válido.
            $token = $this->getValidToken();

            // Se teve erro ao buscar o token, retorna mensagem.
            if (is_array($token) && array_key_exists('error', $token)) {
                return $token;
            }

            // Prepara o statements com 17 caracteres, concatenando agência e conta com o dígito -> 0000.000000000000
            $statements = $bankAccount->account_agency . '.' . Str::padLeft($bankAccount->account_number, 12, '0');

            // Fazer a requisição para obter as transações
            $response = Http::withToken($token)->withHeaders([
                'X-Application-Key' => $this->client_id,
            ])
                ->withOptions([
                    'cert'    => $this->certificate,
                    'ssl_key' => $this->key,
                ])
                ->get($this->base_uri . "/banks/90400888000142/statements/$statements", [
                    'initialDate' => $initial_date,
                    'finalDate'   => $final_date,
                    '_offset'     => '1',
                    '_limit'      => '50',
                ]);

            // Se requisição bem sucedida, segue lógica para formatar retorno das transações.
            if ($response->successful()) {
                // Coloca o response obtido da API em variável json.
                $transactions = $response->json();

                // Checa se obteve transações na chave '_content'.
                if (array_key_exists('_content', $transactions) && count($transactions['_content'])) {

                    // Antes de retornar, formata as transações obtidas
                    return $this->formatTransactions($transactions);
                } else {

                    // Retorna mensagem informando que não obteve transações.
                    return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
                }
            }

            // Caso requisição não tenha sido bem sucedida.
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        } catch (\Exception $e) {
            // Registra o erro no LOG.
            Log::error('SantanderService: Falha na requisição | ' . $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => ' . $e->getMessage());

            // Retorna a mensagem de erro.
            return ['error' => 'Falha na requisição.', 'message' => $e->getMessage()];
        }
    }

    // Método para tratamento de erros para status HTTP diferentes de 200.
    protected function checkResponse(mixed $response): mixed
    {
        // Coloca o response obtido da API em variável json.
        $transactions = $response->json();

        switch ($response->status()) {
            case 200:
                // 200 OK. Requisição bem sucedida.
                // Checa se obteve transações na chave '_content'.
                if (array_key_exists('_content', $transactions) && count($transactions['_content'])) {

                    return $transactions['_content'];
                } else {

                    // Retorna mensagem informando que não obteve transações.
                    return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
                }
                // no break
            case 400:
                //400 Query string obrigatória ausente.
                $messageError = $response->json();
                Log::error('SantanderService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 400 Parâmetro obrigatório ausente - ' . $messageError['message']);

                return ['error' => '400 Parâmetro obrigatório ausente.', 'message' => $messageError['message']];

            case 401:
                // 401 Acesso negado. Verifique o ClientId e o Token utilizado.
                Log::error('SantanderService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 401 Acesso negado - Verifique o ClientId e o Token utilizado.');

                return ['error' => '401 Acesso negado.', 'message' => 'Verifique o ClientId e o Token utilizado.'];

            case 404:
                // 404 Não encontrada. Verifique URL usada.
                Log::error('SantanderService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 404 Não encontrada - Verifique URL usada.');

                return ['error' => '404 Não encontrada.', 'message' => 'Verifique URL usada.'];

            case 422:
                // 422 Erro na Consulta.
                Log::error('SantanderService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 422 Erro na Consulta - A consulta não pode ser realizada.');

                return ['error' => '422 Erro na Consulta.', 'message' => 'A consulta não pode ser realizada.'];

            default:
                // Erro e retorna aviso padrão.
                Log::error('SantanderService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Code: ' . $response->status());

                return ['error' => $response->status() . ' Falha na requisição.', 'message' => 'Falha na requisição.'];
        }
    }

    // Método formata as transações obtidas para padrão comum a todos os bancos.
    protected function formatTransactions(mixed $transactions): mixed
    {
        return collect($transactions)->map(function ($transaction) {
            return [
                'type'            => $transaction['creditDebitType'] == 'DEBITO' ? 'debit' : 'credit',
                'description'     => $transaction['transactionName'],
                'amount'          => $transaction['amount'],
                'date'            => $this->formatDate($transaction['transactionDate']),
                'bank_account_id' => $this->bankAccount->id,
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
