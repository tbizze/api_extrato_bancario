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
                $transactions = $this->santanderService->fetchTransactions($bankAccount, $initial_date, $final_date);

                break;

            case '2': //PagBank
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(2, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
                // Informar a data a obter as transações.
                $transactions = $this->pagBankService->fetchTransactions($bankAccount, $initial_date);

                break;
        }

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }

    public function importAutomaticTransactions(BankAccount $bankAccount): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        // Obtêm a data atual.
        $now = Carbon::now()->subDays(2)->format('Y-m-d');

        switch ($bankAccount->bank->id) {
            case '1': // Santander
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(1, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do Santander obtêm as credenciais (clientId, clientSecret).
                // Informar o período a data a obter as transações: initial_date | final_date.
                $transactions = $this->santanderService->fetchTransactions($bankAccount, $now, $now);

                break;

            case '2': //PagBank
                // Se falhar Checagem das credenciais, retorna mensagem informando.
                if (!$this->checkBankAccount(2, $bankAccount)) {
                    return ['info' => 'Credenciais inválidas.', 'message' => 'Não foi configurado credencias para comunicação com API do banco.'];
                }

                // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
                // Informar a data a obter as transações.
                $transactions = $this->pagBankService->fetchTransactions($bankAccount, $now);

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
