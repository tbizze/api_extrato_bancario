<?php

namespace App\Http\Controllers;

use App\Models\{Company, User};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Listar todos os usuários
        //$users = User::with('company')->get();
        $users = User::get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Carregar empresas para associar ao usuário
        $companies = Company::all();

        return view('users.users-create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validação de dados
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|string|min:8|confirmed',
            'company_id'   => 'required|exists:companies,id',
            'is_superuser' => 'boolean',
        ]);

        // Criar o usuário com senha criptografada
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        // Carregar empresas para associar ao usuário
        $companies = Company::all();

        return view('users.users-edit', compact('user', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Validação de dados
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'password'     => 'nullable|string|min:8|confirmed',
            'company_id'   => 'required|exists:companies,id',
            'is_superuser' => 'boolean',
        ]);

        // Atualizar o usuário, criptografando a senha apenas se fornecida
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Deletar o usuário
        $user->delete();

        return redirect()->route('users.index');
    }
}
