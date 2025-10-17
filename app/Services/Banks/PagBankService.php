<?php

namespace App\Services\Banks;

use App\Models\{BankAccount, PagbankTransaction};
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\{Crypt, Http, Log, Storage};

class PagBankService
{
    protected string $pageSize;

    protected string $baseUrl;

    protected string $token;

    protected BankAccount $bankAccount;

    // Método construtor da classe.
    public function __construct()
    {
        $this->baseUrl  = env('PAGBANK_BASE_URI');
        $this->pageSize = '50';
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

    /*
    Parâmetros:
      pageSize: total máximo de itens por página => mín:1 máx:1000
      pageNumber: de qual página a consulta deve trazer os resultados => padrão:1
      tipoMovimento: 1 => transactional / 2 => financial / 3 => antecipação
     */
    public function fetchAllTransactions(BankAccount $bankAccount, string $date): mixed
    {
        $allTransactions = [];
        $totalPages      = 1;
        $url             = $this->baseUrl . "/transactional/{$date}";

        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        // Faz a requisição para obter as transações.
        $response = Http::withHeaders([
            'Authorization' => "Basic $this->token",
        ])
            ->get($url, [
                'pageNumber' => '1',
                'pageSize'   => $this->pageSize,
            ]);

        // Caso requisição não tenha sucesso.
        if ($response->failed()) {
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        }

        // Coloca o response da API em variável json.
        $transactions = $response->json();

        // Checa se obteve transações na chave 'detalhes'.
        // Adiciona transações retornadas à lista de transações.
        if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {
            $allTransactions = array_merge($allTransactions, $transactions['detalhes']);
        }

        // Checa informações de páginas na chave 'pagination'.
        // Pega o total de páginas.
        if (array_key_exists('pagination', $transactions) && count($transactions['pagination'])) {
            $totalPages = $transactions['pagination']['totalPages'];
        }

        // Caso o número de páginas seja maior que um.
        if ($totalPages > 1) {
            // Executa um Loop até que atinja o número total de páginas.
            for ($page = 2; $page <= $totalPages; $page++) {

                // Faz a requisição para obter as transações da próxima página.
                $response = Http::withHeaders([
                    'Authorization' => "Basic $this->token",
                ])
                    ->get($url, [
                        'pageNumber' => $page,
                        'pageSize'   => $this->pageSize,
                    ]);

                // Checa se obteve transações na chave '_content'.
                // Adiciona transações retornadas à lista de transações.
                $allTransactions = array_merge($allTransactions, $this->checkResponse($response));
            }
        }
        //$filePath = $this->saveTransactionsJson($allTransactions, $date, $bankAccount->id);
        //$filePath = $this->saveTransactionsToTxt($allTransactions, $date, $bankAccount->id);

        //dump($filePath);
        //dd($allTransactions);

        // Checa se foi inserido transações no array.
        // Formata transações obtidas, conforme padrão. Depois retorna.
        if (!empty($allTransactions)) {

            // return $this->formatTransactions($allTransactions);
            return $allTransactions;
        } else {

            // Retorna mensagem informando que não obteve transações.
            return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
        }
    }
    public function fetchAllTransactionsBKP(BankAccount $bankAccount, string $date): mixed
    {
        $allTransactions = [];
        $totalPages      = 1;
        $url             = $this->baseUrl . "/transactional/{$date}";

        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        // Faz a requisição para obter as transações.
        $response = Http::withHeaders([
            'Authorization' => "Basic $this->token",
        ])
            ->get($url, [
                'pageNumber' => '1',
                'pageSize'   => $this->pageSize,
            ]);

        // Caso requisição não tenha sucesso.
        if ($response->failed()) {
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        }

        // Coloca o response da API em variável json.
        $transactions = $response->json();

        // Checa se obteve transações na chave 'detalhes'.
        // Adiciona transações retornadas à lista de transações.
        if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {
            $allTransactions = array_merge($allTransactions, $transactions['detalhes']);
        }

        // Checa informações de páginas na chave 'pagination'.
        // Pega o total de páginas.
        if (array_key_exists('pagination', $transactions) && count($transactions['pagination'])) {
            $totalPages = $transactions['pagination']['totalPages'];
        }

        // Caso o número de páginas seja maior que um.
        if ($totalPages > 1) {
            // Executa um Loop até que atinja o número total de páginas.
            for ($page = 2; $page <= $totalPages; $page++) {

                // Faz a requisição para obter as transações da próxima página.
                $response = Http::withHeaders([
                    'Authorization' => "Basic $this->token",
                ])
                    ->get($url, [
                        'pageNumber' => $page,
                        'pageSize'   => $this->pageSize,
                    ]);

                // Checa se obteve transações na chave '_content'.
                // Adiciona transações retornadas à lista de transações.
                $allTransactions = array_merge($allTransactions, $this->checkResponse($response));
            }
        }
        $filePath = $this->saveTransactionsJson($allTransactions, $date, $bankAccount->id);
        $filePath = $this->saveTransactionsToTxt($allTransactions, $date, $bankAccount->id);

        //dump($filePath);
        //dd($allTransactions);

        // Checa se foi inserido transações no array.
        // Formata transações obtidas, conforme padrão. Depois retorna.
        if (!empty($allTransactions)) {

            return $this->formatTransactions($allTransactions);
        } else {

            // Retorna mensagem informando que não obteve transações.
            return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
        }
    }

    public function fetchAllFinancials(BankAccount $bankAccount, string $date): mixed
    {
        $allTransactions = [];
        $totalPages      = 1;
        $url             = $this->baseUrl . "/financial/{$date}";

        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        // Faz a requisição para obter as transações.
        $response = Http::withHeaders([
            'Authorization' => "Basic $this->token",
        ])
            ->get($url, [
                'pageNumber' => '1',
                'pageSize'   => $this->pageSize,
            ]);

        // Caso requisição não tenha sucesso.
        if ($response->failed()) {
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        }

        // Coloca o response da API em variável json.
        $transactions = $response->json();

        // Checa se obteve transações na chave 'detalhes'.
        // Adiciona transações retornadas à lista de transações.
        if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {
            $allTransactions = array_merge($allTransactions, $transactions['detalhes']);
        }

        // Checa informações de páginas na chave 'pagination'.
        // Pega o total de páginas.
        if (array_key_exists('pagination', $transactions) && count($transactions['pagination'])) {
            $totalPages = $transactions['pagination']['totalPages'];
        }

        // Caso o número de páginas seja maior que um.
        if ($totalPages > 1) {
            // Executa um Loop até que atinja o número total de páginas.
            for ($page = 2; $page <= $totalPages; $page++) {

                // Faz a requisição para obter as transações da próxima página.
                $response = Http::withHeaders([
                    'Authorization' => "Basic $this->token",
                ])
                    ->get($url, [
                        'pageNumber' => $page,
                        'pageSize'   => $this->pageSize,
                    ]);

                // Checa se obteve transações na chave '_content'.
                // Adiciona transações retornadas à lista de transações.
                $allTransactions = array_merge($allTransactions, $this->checkResponse($response));
            }
        }
        //$filePath = $this->saveTransactionsJson($allTransactions, $date, $bankAccount->id);
        //$filePath = $this->saveTransactionsToTxt($allTransactions, $date, $bankAccount->id);

        //dump($filePath);
        //dd($allTransactions);

        // Checa se foi inserido transações no array.
        // Formata transações obtidas, conforme padrão. Depois retorna.
        if (!empty($allTransactions)) {

            // return $this->formatTransactions($allTransactions);
            return $allTransactions;
        } else {

            // Retorna mensagem informando que não obteve transações.
            return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
        }
    }
    public function fetchAllFinancialsBKP(BankAccount $bankAccount, string $date): mixed
    {
        $allTransactions = [];
        $totalPages      = 1;
        $url             = $this->baseUrl . "/financial/{$date}";

        // Método para setar propriedades da classe.
        $this->setProperties($bankAccount);

        // Faz a requisição para obter as transações.
        $response = Http::withHeaders([
            'Authorization' => "Basic $this->token",
        ])
            ->get($url, [
                'pageNumber' => '1',
                'pageSize'   => $this->pageSize,
            ]);

        // Caso requisição não tenha sucesso.
        if ($response->failed()) {
            // Antes de retornar, trata o erro retornado no response.
            return $this->checkResponse($response);
        }

        // Coloca o response da API em variável json.
        $transactions = $response->json();

        // Checa se obteve transações na chave 'detalhes'.
        // Adiciona transações retornadas à lista de transações.
        if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {
            $allTransactions = array_merge($allTransactions, $transactions['detalhes']);
        }

        // Checa informações de páginas na chave 'pagination'.
        // Pega o total de páginas.
        if (array_key_exists('pagination', $transactions) && count($transactions['pagination'])) {
            $totalPages = $transactions['pagination']['totalPages'];
        }

        // Caso o número de páginas seja maior que um.
        if ($totalPages > 1) {
            // Executa um Loop até que atinja o número total de páginas.
            for ($page = 2; $page <= $totalPages; $page++) {

                // Faz a requisição para obter as transações da próxima página.
                $response = Http::withHeaders([
                    'Authorization' => "Basic $this->token",
                ])
                    ->get($url, [
                        'pageNumber' => $page,
                        'pageSize'   => $this->pageSize,
                    ]);

                // Checa se obteve transações na chave '_content'.
                // Adiciona transações retornadas à lista de transações.
                $allTransactions = array_merge($allTransactions, $this->checkResponse($response));
            }
        }
        $filePath = $this->saveTransactionsJson($allTransactions, $date, $bankAccount->id);
        $filePath = $this->saveTransactionsToTxt($allTransactions, $date, $bankAccount->id);

        //dump($filePath);
        //dd($allTransactions);

        // Checa se foi inserido transações no array.
        // Formata transações obtidas, conforme padrão. Depois retorna.
        if (!empty($allTransactions)) {

            return $this->formatTransactions($allTransactions);
        } else {

            // Retorna mensagem informando que não obteve transações.
            return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
        }
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
                    //'pageSize'      => '3',
                ]);

            // Se requisição bem sucedida, segue lógica para formatar retorno das transações.
            if ($response->successful()) {
                // Coloca o response obtido da API em variável json.
                $transactions = $response->json();
                //dd($transactions);

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
            return $this->checkResponse($response);
        } catch (RequestException $e) {

            // Registra o erro no LOG.
            Log::error('PagBankService: Falha na requisição | ' . $this->bankAccount->bank->bank_name .
                '_' . $this->bankAccount->id . ' | Message => ' . $e->getMessage());

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
                if (array_key_exists('detalhes', $transactions) && count($transactions['detalhes'])) {

                    return $transactions['detalhes'];
                } else {

                    // Retorna mensagem informando que não obteve transações.
                    return ['info' => 'Sem transações.', 'message' => 'Não há transações para esse período.'];
                }
                // no break
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
        return collect($transactions)->map(function ($transaction) {
            return [
                'type'            => 'credit',
                'description'     => $this->makeDescriptions($transaction),
                'amount'          => $transaction['valor_total_transacao'],
                'date'            => $transaction['data_prevista_pagamento'] ?? '', // era: data_movimentacao
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

    /**
     * Salva as transações em arquivo JSON.
     *
     * @param  array<int|string, mixed>  $transactions
     * @param  string  $date
     * @param  int  $bankAccountId
     * @return string
     */
    public function saveTransactionsJson(array $transactions, string $date, int $bankAccountId, bool $financial = false): string
    {
        if ($financial == true) {
            $filename = "extratos/account{$bankAccountId}/financial/{$date}.json";
        } else {
            $filename = "extratos/account{$bankAccountId}/{$date}.txt";
        }
        //$filename = "extratos/account{$bankAccountId}/{$date}.json";
        $content = json_encode($transactions, JSON_PRETTY_PRINT);

        Storage::disk('local')->put($filename, $content);

        return storage_path("app/{$filename}");
    }

    /**
     * Salva as transações em arquivo TXT.
     *
     * @param  array<int|string, mixed>  $transactions
     * @param  string  $date
     * @param  int  $bankAccountId
     * @param  bool  $financial
     * @return string
     */
    public function saveTransactionsToTxt(array $transactions, string $date, int $bankAccountId, bool $financial = false): string
    {
        if ($financial == true) {
            $filename = "extratos/account{$bankAccountId}/financial/formatado_{$date}.txt";
        } else {
            $filename = "extratos/account{$bankAccountId}/formatado_{$date}.txt";
        }
        //$filename = "extratos/account{$bankAccountId}/formatado_{$date}.txt";
        $content = "";

        foreach ($transactions as $transaction) {
            $numero_serie_leitor = $transaction['numero_serie_leitor'] ?? '';
            $tx_id               = $transaction['tx_id'] ?? '';

            $content .= "Transação: {$transaction['codigo_transacao']}\n";
            $content .= "Data operação: {$transaction['data_inicial_transacao']} {$transaction['hora_inicial_transacao']}\n";
            $content .= "Data prévia pgto: {$transaction['data_prevista_pagamento']}\n";
            $content .= "Valor: {$transaction['valor_total_transacao']}\n";
            $content .= "Taxa: {$transaction['taxa_intermediacao']}\n";
            $content .= "Parcelas: {$transaction['quantidade_parcelas']}\n";
            $content .= "Meio Pagamento: {$transaction['meio_pagamento']}\n";
            $content .= "Instituicao: {$transaction['instituicao_financeira']}\n";
            $content .= "Tx Id: {$tx_id}\n";
            $content .= "Nº Leitor: {$numero_serie_leitor}\n";
            $content .= "Leitor: {$this->getLeitor($numero_serie_leitor)}\n";
            $content .= "Tipo: {$transaction['arranjo_ur']}\n";
            $content .= str_repeat("-", 50) . "\n\n";
        }

        Storage::disk('local')->put($filename, $content);

        return storage_path("app/{$filename}");
    }

    // Método retorna o nome do leitor conforme número de série.
    public function getLeitor(string $numero_serie_leitor): string
    {
        // Se não tiver número de série, retorna 'nenhum'.
        // Geralmente pix por QRCode emitido sem maquininha.
        if ($numero_serie_leitor === '') {
            // Log para monitorar leitores não mapeados
            Log::info('Transação sem Leitor processada');

            return 'nenhum';
        }

        $list = [
            '542-310-478'  => 'nova-matriz',
            '1731279966'   => 'com-nsa',
            'J9B405443710' => 'dizimo',
            '2130671458'   => 'lojinha',
            '6C282744'     => 'lar-idosos',
        ];

        if (!isset($list[$numero_serie_leitor])) {
            // Log para monitorar leitores não mapeados
            Log::warning('Leitor não mapeado encontrado', [
                'numero_serie' => $numero_serie_leitor,
            ]);
        }

        // Retornar o nome do leitor
        return $list[$numero_serie_leitor] ?? 'desconhecido';
    }

    /**
     * Salva as transações em DB.
     *
     * @param  array<int|string, mixed>  $transactions
     * @param  int  $bankAccountId
     * @return int
     */
    public function saveTransactionsDb(array $transactions, int $bankAccountId): int
    {
        // Se retornado transações, salva do banco de dados.
        foreach ($transactions as $transaction) {
            //dd($transaction);

            $tipo                = $transaction['arranjo_ur'] ?? '';
            $numero_serie_leitor = $transaction['numero_serie_leitor'] ?? '';
            $tx_id               = $transaction['tx_id'] ?? '';
            $valor_tarifas       = $transaction['taxa_intermediacao'] + $transaction['tarifa_intermediacao'];

            if ($tipo == 'PIX') {
                if ($numero_serie_leitor == '' || $numero_serie_leitor == null) {
                    $description = "Recbto PIX QRCode | {$tx_id}";
                } else {
                    $description = "Recbto PIX Leitor| {$tx_id}";
                }
            } else {
                $description = "Recbto {$transaction['instituicao_financeira']} | Cartão ****{$transaction['cartao_holder']} | Meio Pagamento: {$transaction['meio_pagamento']}";
            }
            // Recbto PIX QRCode | PAGS000010000221122091998
            // Recbto PIX Leitor | 20250930201913008660785179669317
            // Recbto VISA ELECTRON | Cartão ****0016 | Meio Pagamento: 11

            // Salva do banco de dados.
            PagbankTransaction::create([
                'bank_account_id'  => $bankAccountId,
                'cod_transacao'    => $transaction['codigo_transacao'],
                'dt_transacao'     => $transaction['data_inicial_transacao'] . ' ' . $transaction['hora_inicial_transacao'],
                'dt_pgto_prevista' => $transaction['data_prevista_pagamento'],
                'valor_transacao'  => $transaction['valor_total_transacao'],
                'valor_tarifas'    => $valor_tarifas,
                'qde_parcelas'     => $transaction['quantidade_parcelas'],
                'description'      => $description,
                'id_leitor'        => $numero_serie_leitor,
                'tx_id'            => $tx_id,
                'tipo'             => $transaction['arranjo_ur'],
                'status'           => 'previsao',
            ]);
        }
        $number_transactions_import = count($transactions);

        // Retornar o nome do leitor
        return $number_transactions_import;
    }

    /**
     * Atualiza as transações em DB.
     *
     * @param  array<int|string, mixed>  $transactions
     * @return int
     */
    public function updateTransactionsDb(array $transactions): int
    {
        // Se retornado transações, salva do banco de dados.
        foreach ($transactions as $transaction) {
            $data = PagbankTransaction::query()
                ->where('cod_transacao', $transaction['codigo_transacao'])
                ->first();

            // Garantir que os valores sejam float antes da soma e comparação
            $taxa_intermediacao   = (float)($transaction['taxa_intermediacao'] ?? 0);
            $tarifa_intermediacao = (float)($transaction['tarifa_intermediacao'] ?? 0);

            $valor_tarifas     = $taxa_intermediacao + $tarifa_intermediacao;
            $valor_tarifas_old = (float)($data->valor_tarifas ?? 0.00);

            if ($data && $valor_tarifas > $valor_tarifas_old) {
                $notes = 'Tarifa atualizada';
            } else {
                $notes = '';
            }

            if ($data) {
                $data->update([
                    'status' => 'confirmado',
                    'notes'  => $notes,
                ]);
            }
        }
        //dump($transaction);
        //dd($data->toArray());

        $number_transactions_import = count($transactions);

        // Retornar o nome do leitor
        return $number_transactions_import;
    }
}
