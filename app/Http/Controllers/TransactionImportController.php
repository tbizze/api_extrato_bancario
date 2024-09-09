<?php

namespace App\Http\Controllers;

use App\Models\{BankAccount, Transaction};
use App\Services\TransactionManagerService;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class TransactionImportController extends Controller
{
    protected mixed $transactionManager;

    public function __construct(TransactionManagerService $transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }

    // Método para renderizar view para definir parâmetros para importação de transações.
    public function select(BankAccount $bankAccount): View
    {
        // Passa o BankAccount para a view.
        // para mostrar qual conta bancária estará solicitando a importação.
        return view('transactions.import', compact('bankAccount'));
    }

    // Método para receber da view o pedido de importação de transações.
    public function import(BankAccount $bankAccount, Request $request): RedirectResponse
    {
        // Regras de validação para a data inicial de importação.
        $rules = [
            'initial_date' => 'required|date',
        ];

        // Regras de validação para data final de importação.
        if ($bankAccount->bank->id == 1) {
            $rules['final_date'] = 'required|date'; // Torna obrigatório se 1 --> Santander
        } else {
            $rules['final_date'] = 'nullable|date'; // Deixa opcional se 2 --> PagBank
        }

        // Validação de dados
        $validated = $request->validate($rules);

        // Chama o serviço que gerencia requisições às APIs dos diversos bancos.
        // Passa os parâmetros recebidos.
        $transactions = $this->transactionManager
            ->importTransactions($bankAccount, $validated['initial_date'], $validated['final_date']);

        // Verifica o retorno do TransactionManagerService.
        if (isset($transactions) && is_array($transactions) && count($transactions) > 0) {
            // Se retornado transações, salva do banco de dados.
            Transaction::insert($transactions);
            $number_transactions_import = count($transactions);
        } else {
            // Se não retornou transações, emite uma mensagem avisando.
            return redirect()->route('bank-accounts.transactions.select', $bankAccount)->with('message', "Não foi encontrado transações para importar!");
        }

        // Redireciona para lista de transações. Exibe mensagem de sucesso.
        return redirect()->route('bank-accounts.transactions.index', $bankAccount)
            ->with('success', "Foram importadas $number_transactions_import transações com sucesso!");
    }
}
