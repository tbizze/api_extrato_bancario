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

    public function importTransactions(BankAccount $bankAccount)
    {
        //dd('importTransactions', $bankAccount->bank->id);

        $transactions = [];

        switch ($bankAccount->bank->id) {
            case '1': // Santander
                // Ao chamar o método 'fetchTransactions' passar os parâmetros necessários para a requisição à API.
                // Passar também o objeto BankAccount, para que com ele o serviço do Santander possa obter
                // os dados do cliente (clientId, clientSecret).

                // $clientId = $bankAccount->bank->client_id;
                // $clientSecret = $bankAccount->bank->client_secret;
                $transactions = $this->santanderService->fetchTransactions($bankAccount);

                break;

            case '2': //PagBank
                //$transactions = $this->pagBankService->fetchTransactions($bankAccount);
                break;
        }

        return $transactions;

        // Salvar as transações no banco de dados
        //Transaction::insert($transactions);
    }
}
