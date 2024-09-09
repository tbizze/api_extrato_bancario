<?php

namespace App\Services;

use App\Models\BankAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PagbankService
{
    protected mixed $client;

    protected string $baseUrl;

    protected string $clientId;

    protected string $token;

    // Método construtor do client.
    // Ao instanciar, deve enviar o token através da chave 'authorization' no headers.
    public function __construct()
    {
        $this->baseUrl  = env('PAGBANK_BASE_URI');
        $this->clientId = env('PAGBANK_CLIENT_ID');
        $this->token    = env('PAGBANK_TOKEN');

        $this->client = new Client();
    }

    // Método para validar o token.
    // Deve enviar via parâmetros de URL o clientId e o oken.
    public function checkToken(): mixed
    {
        try {
            $response = $this->client->GET($this->baseUrl . '/users/' . $this->clientId . '/token/' . $this->token, [
                'headers' => [
                    'accept' => 'application/json',
                ],
            ]);

            $data         = json_decode($response->getBody(), true);
            $codeResponse = $data['code'];

            if ($codeResponse != 200) {
                /* Retorna um JSON com:
                   'code' => 404
                   'codeValue' => NOT FOUND
                   'message' => 'Os parâmetros informados não são válidos.'
                 */
                return response()->json(['error' => 'Token inválido.', 'message' => $data['code'] . ' ' . $data['codeValue'] . ' -> ' . $data['message']], 400);
            }

            /* Retorna um JSON com:
                'code' => 404
                'codeValue' => NOT FOUND
                'message' => 'Os parâmetros informados não são válidos.'
            */
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()], 400);
        }
    }

    // Método que faz requisição do extrato a API Edi do Pagbank.
    // Deve passar no header a credencial. Deve concatenar como string 'clientId:token'.
    // Deve receber como parâmetros o tipo de movimento, a data do movimento e a página.
    public function getExtrato(int $tipo, string $data, int $page): mixed
    {
        $credentials = base64_encode("$this->clientId:$this->token");
        $params      = "tipoMovimento=$tipo&dataMovimento=$data&pageNumber=$page&pageSize=20";

        try {
            $response = $this->client->GET($this->baseUrl . '/2.01/movimentos?' . $params, [
                'headers' => [
                    'Authorization' => "Basic $credentials",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()]);
        }
    }

    // Método faz requisição do extrato a API Edi do Pagbank.
    // Passar no header da requisição a credencial. Concatenar 'clientId:token' e codificar esta string com Base64.
    // PARÂMETRO: data do movimento.
    public function fetchTransactions(BankAccount $bankAccount, string $data): mixed
    {
        // Implementar aqui a lógica para buscar as transações do banco de dados.
        // Utilizar a $bankAccount para filtrar as transações do banco.
        // Retornar um array com as transações.

        // Prepara a credencial concatenando 'clientId:token' e codificando com Base64.
        $credentials = base64_encode("$this->clientId:$this->token");
        // Prepara os parâmetros da requisição.
        $params = "tipoMovimento=2&dataMovimento=$data&pageNumber=1&pageSize=20";

        try {
            // Faz a requisição com a credencial e os parâmetros.
            $response = $this->client->GET($this->baseUrl . '/2.01/movimentos?' . $params, [
                'headers' => [
                    'Authorization' => "Basic $credentials",
                ],
            ]);

            // Recupera na variável os dados da resposta da API, decodificando.
            $dados = json_decode($response->getBody(), true);

            // Ao invés de usar o response da API, usa dados de exemplo -> getValA() | getValB()
            //$dados = json_decode($this->getValA(), true);

            // Para retornar, formata as transações obtidas, conforme padrão.
            return $this->formatTransactions($dados, $bankAccount);
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
                //'complement' => $transaction['codigo_transacao'],
                //'bank' => $bankAccount->bank->bank_name,
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
