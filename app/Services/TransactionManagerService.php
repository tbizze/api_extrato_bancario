<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Services\Banks\{PagBankService, SantanderService};
use Carbon\Carbon;

class TransactionManagerService
{
    protected mixed $santanderService;

    protected mixed $pagBankService;

    public function __construct(SantanderService $santanderService, PagBankService $pagBankService)
    {
        $this->santanderService = $santanderService;
        $this->pagBankService   = $pagBankService;
    }

    // Método que gerencia as requisições a serem feitas às APIs dos bancos.
    // Conforme o BankAccount, chama o método 'fetchTransactions' da API correspondente.
    // Recebe BankAccount | initial_date | final_date.
    public function importTransactions(BankAccount $bankAccount, string $initial_date, string|null $final_date): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        switch ($bankAccount->bank->id) {
            case '1': // Santander
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(1, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do Santander obtêm as credenciais (clientId, clientSecret).
                // Informar o período a data a obter as transações: initial_date | final_date.
                $transactions = $this->santanderService->fetchAllTransactions($bankAccount, $initial_date, $final_date);

                break;

            case '2': //PagBank
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(2, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
                // Informar a data a obter as transações.
                $transactions = $this->pagBankService->fetchAllTransactions($bankAccount, $initial_date);

                break;
        }

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }

    public function importFinancials(BankAccount $bankAccount, string $initial_date, string|null $final_date): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        // Se falhar Checagem das credenciais, retorna mensagem informando.
        if (!$this->checkBankAccount(2, $bankAccount)) {
            return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
        }

        // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
        // Informar a data a obter as transações.
        $transactions = $this->pagBankService->fetchAllFinancials($bankAccount, $initial_date);

        //dd($initial_date);

        // Salva arquivo txt e json com as transações obtidas.
        $y = $this->pagBankService->saveTransactionsToTxt($transactions, $initial_date, $bankAccount->id, true);
        $y = $this->pagBankService->saveTransactionsJson($transactions, $initial_date, $bankAccount->id, true);
        //dump($y);

        // Salva no banco de dados as transações obtidas.
        $x = $this->pagBankService->updateTransactionsDb($transactions, $bankAccount);
        //dd($x);

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }

    public function importAutomaticPagbank(BankAccount $bankAccount, string $initial_date, string|null $final_date): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        // Se falhar Checagem das credenciais, retorna mensagem informando.
        if (!$this->checkBankAccount(2, $bankAccount)) {
            return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
        }

        /* Importar transações
           Argumento: BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
           Argumento: Data. Período a obter as transações.
        */
        $transactions = $this->pagBankService->fetchAllTransactions($bankAccount, $initial_date);

        // Salva no banco de dados as transações obtidas.
        $qdeTransacoes = $this->pagBankService->saveTransactionsDb($transactions, $bankAccount);

        // Salva arquivo txt e json com as transações obtidas.
        $this->pagBankService->saveTransactionsToTxt($transactions, $initial_date, $bankAccount->id);
        $this->pagBankService->saveTransactionsJson($transactions, $initial_date, $bankAccount->id);

        /* Importar transações pagas
           Argumento: BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
           Argumento: Data. Período a obter as transações.
        */
        $transactions = $this->pagBankService->fetchAllFinancials($bankAccount, $initial_date);

        // Atualiza no banco de dados as transações obtidas.
        $qdeTransacoesPagas = $this->pagBankService->updateTransactionsDb($transactions, $bankAccount);

        // Salva arquivo txt e json com as transações obtidas.
        $this->pagBankService->saveTransactionsToTxt($transactions, $initial_date, $bankAccount->id, true);
        $this->pagBankService->saveTransactionsJson($transactions, $initial_date, $bankAccount->id, true);

        //dd($qdeTransacoes, $qdeTransacoesPagas);

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }

    public function importAutomaticTransactions(BankAccount $bankAccount): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        // Obtêm a data atual. Subtrai um dia.
        $now = Carbon::now()->subDays(1)->format('Y-m-d');

        switch ($bankAccount->bank->id) {
            case '1': // Santander
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(1, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do Santander obtêm as credenciais (clientId, clientSecret).
                // Informar o período a data a obter as transações: initial_date | final_date.
                $transactions = $this->santanderService->fetchAllTransactions($bankAccount, $now, $now);

                break;

            case '2': //PagBank
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(2, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
                // Informar a data a obter as transações.
                $transactions = $this->pagBankService->fetchAllTransactions($bankAccount, $now);

                break;
        }

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }
    public function checkBankAccount(int $bank_id, BankAccount $bankAccount): bool
    {
        switch ($bank_id) {
            case '1': //Santander
                // Lógica para testar campos obrigatórios.
                // Se falhar algum, retorna false
                if ($bankAccount->certificate_path == '' || $bankAccount->certificate_path == null) {
                    // dd('certificate_path');
                    return false;
                }

                if ($bankAccount->key_path == '' || $bankAccount->key_path == null) {
                    // dd('key_path');
                    return false;
                }

                if ($bankAccount->client_id == '' || $bankAccount->client_id == null) {
                    // dd('client_id');
                    return false;
                }

                if ($bankAccount->client_secret == '' || $bankAccount->client_secret == null) {
                    // dd('client_secret');
                    return false;
                }

                return true;
                //break;
            case '2': //PagBank
                if ($bankAccount->client_id == '' || $bankAccount->client_id == null) {
                    // dd('client_id');
                    return false;
                }

                if ($bankAccount->client_secret == '' || $bankAccount->client_secret == null) {
                    // dd('client_secret');
                    return false;
                }

                return true;
                //break;
            default:
                return false;
                //break;
        }
    }
}
