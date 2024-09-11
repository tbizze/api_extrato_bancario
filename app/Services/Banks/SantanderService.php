<?php

namespace App\Services\Banks;

use App\Models\{AccessToken, BankAccount};
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\{Crypt, Http};
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
        $this->certificate   = storage_path('app/private/certificates/' . $bankAccount->certificate_path);
        $this->key           = storage_path('app/private/keys/' . $bankAccount->key_path);
        $this->client_id     = Crypt::decryptString($bankAccount->client_id);
        $this->client_secret = Crypt::decryptString($bankAccount->client_secret);
    }

    // Método envia as credenciais para o endpoint de autenticação do OAuth.
    // Retorna o token de acesso.
    private function generateToken(): string
    {
        // Fazer a requisição para obter o token de acesso
        $response = Http::asForm()->withOptions([
            'cert'    => $this->certificate,
            'ssl_key' => $this->key,
        ])->post($this->base_uri_oauth . '/auth/oauth/v2/token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
        ]);

        $data = $response->json();

        // Chama método para armazenar dados do token.
        $this->storeAccessToken($data);

        // Retorna o token criado.
        return $data['access_token'];
    }

    // Método para buscar e validar último token.
    // Se inválido, requisita novo.
    private function getValidToken(): string
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

            // Recupera na variável os dados da resposta da API, decodificando.
            $data = $response->json();

            // Verifica se retornou transações na chave '_content'.
            if (array_key_exists('_content', $data)) {
                // Para retornar, formata as transações obtidas, conforme padrão.
                return $this->formatTransactions($data, $bankAccount);
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
