<?php

namespace App\Services\Banks;

use App\Models\BankAccount;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\{Crypt, Http, Log};

class PagBankService
{
    protected string $baseUrl;

    protected string $token;

    protected BankAccount $bankAccount;

    // Método construtor da classe.
    public function __construct()
    {
        $this->baseUrl = env('PAGBANK_BASE_URI');
    }

    // Método define propriedades da classe, a partir BankAccount recebido como argumento.
    private function setProperties(BankAccount $bankAccount): void
    {
        // Seta os dados do BankAccount na classe.
        $this->bankAccount = $bankAccount;

        // Resgata user e password do BD, e descriptografa.
        $user     = Crypt::decryptString($bankAccount->client_id);
        $password = Crypt::decryptString($bankAccount->client_secret);

        // Define propriedade credencial. Concatena 'user:password' e codifica com Base64.
        $this->token = base64_encode($user . ':' . $password);
    }

    // Método faz requisição do extrato a API Edi do Pagbank.
    // Passar no header da requisição a credencial. Concatenar 'user:password' e codificar esta string com Base64.
    // PARÂMETRO: data do movimento.
    public function fetchTransactions(BankAccount $bankAccount, string $date): mixed
    {
        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        try {
            // Fazer a requisição para obter as transações
            $response = Http::withHeaders([
                'Authorization' => "Basic $this->token",
            ])
                ->get($this->baseUrl . '/2.01/movimentos', [
                    'tipoMovimento' => '2',
                    'dataMovimento' => $date,
                    'pageNumber'    => '1',
                    'pageSize'      => '20',
                ]);

            // Se requisição bem sucedida, segue lógica para formatar retorno das transações.
            if ($response->successful()) {
                // Coloca o response obtido da API em variável json.
                $transactions = $response->json();

                // Checa se obteve transações na chave 'detalhes'.
                if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {
                    //dd($transactions);

                    // Formata transações obtidas, conforme padrão. Depois retorna.
                    return $this->formatTransactions($transactions);
                } else {

                    // Retorna mensagem informando que não obteve transações.
                    return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
                }
            }

            // Caso requisição não tenha sido bem sucedida.
            // Antes de retornar, trata o erro retornado no response.
            return $this->errorMessage($response);
        } catch (RequestException $e) {

            // Registra o erro no LOG.
            Log::error('PagBankService: Falha na requisição | ' . $this->bankAccount->bank->bank_name .
                '_' . $this->bankAccount->id . ' | Message => ' . $e->getMessage());

            // Retorna a mensagem de erro.
            return ['error' => 'Falha na requisição.', 'message' => $e->getMessage()];
        }
    }

    // Método para tratamento de erros para status HTTP diferentes de 200.
    protected function errorMessage(mixed $response): mixed
    {
        switch ($response->status()) {
            case 400:
                //400 Query string obrigatória ausente.
                $messageError = $response->json();
                Log::error('PagBankService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 400 Parâmetro obrigatório ausente - ' . $messageError['message']);

                return ['error' => '400 Parâmetro obrigatório ausente.', 'message' => $messageError['message']];

            case 401:
                // 401 Acesso negado. Verifique o ClientId e o Token utilizado.
                Log::error('PagBankService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 401 Acesso negado - Verifique o ClientId e o Token utilizado.');

                return ['error' => '401 Acesso negado.', 'message' => 'Verifique o ClientId e o Token utilizado.'];

            case 404:
                // 404 Não encontrada. Verifique URL usada.
                Log::error('PagBankService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 404 Não encontrada - Verifique URL usada.');

                return ['error' => '404 Não encontrada.', 'message' => 'Verifique URL usada.'];

            case 422:
                // 422 Erro na Consulta.
                Log::error('PagBankService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Message => 422 Erro na Consulta - A consulta não pode ser realizada.');

                return ['error' => '422 Erro na Consulta.', 'message' => 'A consulta não pode ser realizada.'];

            default:
                // Erro e retorna aviso padrão.
                Log::error('PagBankService: Falha na requisição | ' .
                    $this->bankAccount->bank->bank_name . '_' . $this->bankAccount->id . ' | Code: ' . $response->status());

                return ['error' => $response->status() . ' Falha na requisição.', 'message' => 'Falha na requisição.'];
        }
    }

    // Método formata as transações obtidas para padrão comum a todos os bancos.
    // Para preparar a chave 'description', chama o método makeDescriptions.
    protected function formatTransactions(mixed $transactions): mixed
    {
        return collect($transactions['detalhes'])->map(function ($transaction) {
            return [
                'type'            => 'credit',
                'description'     => $this->makeDescriptions($transaction),
                'amount'          => $transaction['valor_total_transacao'],
                'date'            => $transaction['data_movimentacao'],
                'bank_account_id' => $this->bankAccount->id,
            ];
        })->toArray();
    }

    // Método monta descrição, concatenando informações da transação obtida do banco.
    // Conforme o tipo da transação, utiliza dados específicos (PIX QR | Maquininha | Diverso).
    protected function makeDescriptions(mixed $transaction): string
    {
        // Recebimento via PIX QRCode.
        // Ex.: Recebido PIX QRCode - PAGS000010000221122004790
        if (isset($transaction['tx_id']) && trim($transaction['tx_id']) !== '') {
            return 'Recebido PIX QRCode - ' . $transaction['tx_id'];
        }

        // Recebimento via maquininha.
        // Ex.: Recebido leitor J9B405443710 - NSU:424512910973 - VISA ELECTRON
        // Ex.: Recebido leitor 542-310-478 - NSU:424510753300 - ELO
        if (isset($transaction['numero_serie_leitor']) && trim($transaction['numero_serie_leitor']) !== '') {
            return 'Recebido leitor: ' . $transaction['numero_serie_leitor'] . ' - NSU:' . $transaction['nsu'] . ' - ' . $transaction['instituicao_financeira'];
        }

        // Outros tipos de recebimento.
        return 'Recebimento de valor';
    }
}
