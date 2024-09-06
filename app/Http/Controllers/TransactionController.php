<?php

namespace App\Http\Controllers;

use App\Models\{BankAccount, Transaction};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BankAccount $bankAccount): View
    {
        // Verificar se o usuário tem permissão para visualizar a conta bancária.
        $this->authorize('index', $bankAccount);

        // Listar todas as transações da conta bancária
        $transactions = $bankAccount->transactions;

        //dd($transactions->bankAccount);

        //dd($bankAccount, $transactions);

        return view('transactions.index', compact('transactions', 'bankAccount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(BankAccount $bankAccount): View
    {
        $this->authorize('view', $bankAccount);

        return view('transactions.create', compact('bankAccount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('view', $bankAccount);

        // Validação dos dados
        $validated = $request->validate([
            'type'        => 'required|in:credit,debit',
            'amount'      => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'date'        => 'required|date',
        ]);

        // Criar uma nova transação para a conta bancária
        Transaction::create([
            'bank_account_id' => $bankAccount->id,
            'type'            => $validated['type'],
            'amount'          => $validated['amount'],
            'description'     => $validated['description'],
            'date'            => $validated['date'],
        ]);

        return redirect()->route('bank-accounts.transactions.index', $bankAccount);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('transactions.edit', compact('bankAccount', 'transaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        // Validação dos dados
        $validated = $request->validate([
            'type'        => 'required|in:credit,debit',
            'amount'      => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'date'        => 'required|date',
        ]);

        // Atualizar a transação
        $transaction->update($validated);

        return redirect()->route('bank-accounts.transactions.index', $bankAccount);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount, Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('bank-accounts.transactions.index', $bankAccount);
    }
}
