<?php

namespace App\Jobs;

use App\Models\{BankAccount, TaskLog};
use App\Services\TransactionManagerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class ImportPagbankTransactionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $date)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionManagerService $transactionManager): void
    {
        // Obtém todas as contas bancárias cadastradas.
        $bankAccounts = BankAccount::query()
            ->where('bank_id', '=', 2) // Apenas contas Pagbank.
            ->where('id', '=', 3) // Apenas a conta de ID 2: Paróquia / ID 3 Lar dos Idosos.
            ->get();

        // Para cada conta, realiza a importação das transações
        foreach ($bankAccounts as $bankAccount) {

            // Registra um log
            Log::info('>>> Start task automatic import: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id);

            // Chama o serviço que gerencia requisições às APIs dos diversos bancos.
            // Passa como parâmetro o BankAccount.
            $info = $transactionManager->importAutomaticPagbank($bankAccount, $this->date);

            $transactions = ($info['transactions']);

            // Verifica o retorno do TransactionManagerService.
            if (isset($transactions) && is_array($transactions) && array_key_exists('error', $transactions)) {

                // Se ocorreu erro, grava LOG avisando.
                Log::error('ERROR AUTOMATIC IMPORT: ' . $transactions['error']);

                // Registrar TaskLog de falha
                $status = 'Erro: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - Falha ao processar importação automática >> ' . $transactions['error'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            } elseif (isset($transactions) && is_array($transactions) && array_key_exists('info', $transactions)) {

                // Se não retornou transações, grava LOG avisando.
                Log::info('FAIL AUTOMATIC IMPORT: ' . $transactions['info']);

                // Registrar TaskLog de falha
                $status = 'Info: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - ' . $transactions['message'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            } else {

                // Se retornado transações, salva do banco de dados.

                // Registra um log
                Log::info('AUTOMATIC IMPORT: ' . $transactions['message']);

                // Registrar TaskLog de falha
                $status = 'Success: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - ' . $transactions['message'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            }

            $financials = ($info['financials']);

            if (isset($financials) && is_array($financials) && array_key_exists('error', $financials)) {

                // Se ocorreu erro, grava LOG avisando.
                Log::error('ERROR AUTOMATIC IMPORT: ' . $financials['error']);

                // Registrar TaskLog de falha
                $status = 'Erro: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - Falha ao processar importação automática >> ' . $financials['error'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            } elseif (isset($financials) && is_array($financials) && array_key_exists('info', $financials)) {

                // Se não retornou transações, grava LOG avisando.
                Log::info('FAIL AUTOMATIC IMPORT: ' . $financials['info']);

                // Registrar TaskLog de falha
                $status = 'Info: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - ' . $financials['message'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            } else {

                // Se retornado transações, salva do banco de dados.

                // Registra um log
                Log::info('AUTOMATIC IMPORT: ' . $financials['message']);

                // Registrar TaskLog de falha
                $status = 'Success: ' . $bankAccount->bank->bank_name . '|' . $bankAccount->id . '|' . $this->date . ' - ' . $financials['message'];
                TaskLog::create([
                    'task_name' => 'Task: Importação Automática',
                    'status'    => $status,
                ]);
            }

            // Enviar notificação para todos os usuários
            // $users = User::all();

            // foreach ($users as $user) {
            //     $user->notify(new TaskStatusNotification($status));
            // }
        }
    }
}
