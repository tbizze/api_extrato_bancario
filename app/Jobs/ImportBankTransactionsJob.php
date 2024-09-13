<?php

namespace App\Jobs;

use App\Models\{BankAccount, Transaction};
use App\Services\TransactionManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Log};

class ImportBankTransactionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected mixed $transactionManager;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //$this->transactionManager = $transactionManager;
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionManagerService $transactionManager): void
    {
        // Obtém todas as contas bancárias cadastradas.
        $bankAccounts = BankAccount::all();

        // Para cada conta, realiza a importação das transações
        foreach ($bankAccounts as $bankAccount) {

            // Registra um log
            Log::info('>>> Start task automatic import: ' . $bankAccount->bank->bank_name . $bankAccount->id);

            // Chama o serviço que gerencia requisições às APIs dos diversos bancos.
            // Passa como parâmetro o BankAccount.
            $transactions = $transactionManager->importAutomaticTransactions($bankAccount);

            // Verifica o retorno do TransactionManagerService.
            if (isset($transactions) && is_array($transactions) && array_key_exists('error', $transactions)) {

                // Se ocorreu erro, grava LOG avisando.
                Log::error('ERROR AUTOMATIC IMPORT: ' . $transactions['error']);
            } elseif (isset($transactions) && is_array($transactions) && array_key_exists('info', $transactions)) {

                // Se não retornou transações, grava LOG avisando.
                Log::info('FAIL AUTOMATIC IMPORT: ' . $transactions['info']);
            } else {

                // Se retornado transações, salva do banco de dados.
                //Transaction::insert($transactions);
                $number_transactions_import = count($transactions);
                // Registra um log
                Log::info("AUTOMATIC IMPORT: Efetuado $number_transactions_import importações de transações no BD.");

                foreach ($transactions as $transaction) {
                    // Salva do banco de dados.
                    Transaction::create($transaction);
                }
            }
        }
    }
}
