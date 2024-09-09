<?php

namespace App\Services;

use App\Models\BankAccount;

class TransactionManagerService
{
    protected mixed $santanderService;

    protected mixed $pagBankService;

    public function __construct(SantanderService $santanderService, PagbankService $pagBankService)
    {
        $this->santanderService = $santanderService;
        $this->pagBankService   = $pagBankService;
    }

    // Método que gerencia as requisições a serem feitas às APIs dos bancos.
    // Conforme o BankAccount, chama o método 'fetchTransactions' da API correspondente.
    // Recebe BankAccount | initial_date | final_date.
    public function importTransactions(BankAccount $bankAccount, string $initial_date, string $final_date): mixed
    {
        // Array para armazenar as transações retornadas da API.
        $transactions = [];

        switch ($bankAccount->bank->id) {
            case '1': // Santander
                // Informar o BankAccount. Com ele o serviço do Santander obtêm as credenciais (clientId, clientSecret).
                // Informar o período a data a obter as transações: initial_date | final_date.
                $transactions = $this->santanderService->fetchTransactions($bankAccount, $initial_date, $final_date);

                break;

            case '2': //PagBank
                // Informar o BankAccount. Com ele o serviço do PagBank obtêm as credenciais (clientId, token).
                // Informar a data a obter as transações.
                $transactions = $this->pagBankService->fetchTransactions($bankAccount, $initial_date);

                break;
        }

        // Devolve ao controller os dados obtidos da API do banco.
        return $transactions;
    }
}
