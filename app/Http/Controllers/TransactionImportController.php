<?php

namespace App\Http\Controllers;

use App\Models\{BankAccount, Transaction};
use App\Services\TransactionManagerService;

class TransactionImportController extends Controller
{
    protected $transactionManager;

    public function __construct(TransactionManagerService $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    public function import(BankAccount $bankAccount)
    {
        //$bankAccount = BankAccount::findOrFail($request->input('bank_account_id'));

        // Importa as transações do banco associado
        $transactions = $this->transactionManager->importTransactions($bankAccount);
        //dd($transactions, 'complement');

        // Salvar as transações no banco de dados
        //Transaction::insert($transactions);

        $x = 0;

        foreach ($transactions as $item) {
            $x++;
            dump($item);
            Transaction::create($item);
        }

        //dd($x);

        return redirect()->route('bank-accounts.transactions.index', $bankAccount)->with('success', 'Transações importadas com sucesso!');
    }
}
