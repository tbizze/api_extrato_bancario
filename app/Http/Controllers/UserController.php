<?php

namespace App\Http\Controllers;

use App\Models\{Company, User};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Hash};
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

        // Carregar tipos de usuário
        $tipos = $this->getTipos();

        return view('users.users-create', compact('companies', 'tipos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['password' => '123']);

        // Validação de dados.
        $validated = $request->validate([
            'name'         => 'required|string|min:3|max:255',
            'email'        => 'required|email|unique:users',
            'is_superuser' => 'required|digits_between:0,1',
            'company_id'   => 'required|exists:companies,id',
            'password'     => 'required', // required|string|min:8|confirmed
        ]);

        // Trata a senha para que senha armazenada com criptografia.
        $validated['password'] = Hash::make($validated['password']);

        // Salvar o novo usuário.
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

        // Carregar tipos de usuário
        $tipos = $this->getTipos();

        return view('users.users-edit', compact('user', 'companies', 'tipos'));
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
            'is_superuser' => 'required|digits_between:0,1',
            'company_id'   => 'required|exists:companies,id',
            'password'     => 'nullable', //required|string|min:8|confirmed
        ]);

        // Se senha foi digitado, trata-a para seja armazenada com criptografia.
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        // Atualizar o usuário.
        $user->update($validated);

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Verifica se não é tentativa de deletar o próprio usuário.
        if (Auth::user()->id == $user->id) {
            return redirect()->route('users.index')->with('error', 'Você não pode deletar seu próprio usuário.');
        }

        // Deletar o usuário
        $user->delete();

        return redirect()->route('users.index');
    }

    public function getTipos(): mixed
    {
        $data = [
            ['id' => 0, 'name' => 'Comum'],
            ['id' => 1, 'name' => 'Super Admin'],
        ];

        return $data;
    }
}
