<?php

namespace App\Http\Controllers;

use App\Models\{Bank, BankAccount};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Pegar as contas bancárias da empresa do usuário autenticado
        $bankAccounts = Auth::user()->company->bankAccounts;
        //$bankAccounts = BankAccount::where('company_id', Auth::user()->company_id)->get();

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Carregar banks para associar à conta bancária
        $banks = Bank::all();

        return view('bank-accounts.create', compact('banks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validação de dados
        $validated = $request->validate([
            'bank_id'        => 'required|integer',
            'account_agency' => 'required|digits_between:3,10',
            'account_number' => 'required|digits_between:3,20',
            'bank_name'      => 'required|string|min:5|max:150',
        ]);

        // Criar a conta bancária relacionada à empresa do usuário
        Auth::user()->company->bankAccounts()->create($validated);

        // $validated['company_id'] = Auth::user()->company_id;
        // BankAccount::create($validated);

        return redirect()->route('bank-accounts.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        // OPÇÃO 1: Policy --> Garantir que o usuário só pode editar contas da sua empresa
        $this->authorize('view', $bankAccount);

        // OPÇÃO 2: Lógica --> Verificar se a conta bancária pertence à empresa do usuário
        // if ($bankAccount->company_id !== Auth::user()->company_id) {
        //     abort(403, 'Você não tem permissão para acessar esta conta bancária.');
        // }

        // Carregar banks para associar à conta bancária
        $banks = Bank::all();

        return view('bank-accounts.edit', compact('bankAccount', 'banks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {

        // OPÇÃO 1: Policy --> Garantir que o usuário só pode editar contas da sua empresa
        $this->authorize('update', $bankAccount);

        // OPÇÃO 2: Lógica --> Garantir que a conta pertence à empresa do usuário
        // if ($bankAccount->company_id !== Auth::user()->company_id) {
        //     abort(403, 'Você não tem permissão para acessar esta conta bancária.');
        // }

        $validated = $request->validate([
            'bank_id'        => 'required|integer',
            'account_agency' => 'required|digits_between:3,10',
            'account_number' => 'required|digits_between:3,20',
            'bank_name'      => 'required|string|min:5|max:150',
        ]);

        // Atualizar a conta bancária
        $bankAccount->update($validated);

        return redirect()->route('bank-accounts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        // OPÇÃO 1: Policy --> Garantir que o usuário só pode deletar contas da sua empresa
        $this->authorize('delete', $bankAccount);

        // OPÇÃO 2: Lógica --> Garantir que a conta pertence à empresa do usuário
        // if ($bankAccount->company_id !== Auth::user()->company_id) {
        //     abort(403, 'Você não tem permissão para acessar esta conta bancária.');
        // }

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index');
    }
}
