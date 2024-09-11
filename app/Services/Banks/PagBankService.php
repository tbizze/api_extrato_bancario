<?php

namespace App\Services\Banks;

use App\Models\BankAccount;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\{Crypt, Http};

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

            // Recupera na variável os dados da resposta da API, decodificando.
            $data = $response->json();

            // Para retornar, formata as transações obtidas, conforme padrão.
            return $this->formatTransactions($data, $bankAccount);
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()]);
        }
    }

    // Método formata as transações obtidas para padrão comum a todos os bancos.
    // Para preparar a chave 'description', chama o método makeDescriptions.
    protected function formatTransactions(mixed $transactions, BankAccount $bankAccount): mixed
    {
        return collect($transactions['detalhes'])->map(function ($transaction) use ($bankAccount) {
            return [
                'type'            => 'credit',
                'description'     => $this->makeDescriptions($transaction),
                'amount'          => $transaction['valor_total_transacao'],
                'date'            => $transaction['data_movimentacao'],
                'bank_account_id' => $bankAccount->id,
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
