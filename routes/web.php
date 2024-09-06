<?php

use App\Http\Controllers\{BankAccountController, CompanyController, HomeController, PagbankController, ProfileController, SantanderController, TransactionController, TransparenciaController, UserController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (app()->isLocal()) {
        // Para fins de teste, faça login como usuário com ID 1 no desenvolvimento local.
        auth()->loginUsingId(1);

        return to_route('dashboard');
    }

    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// A middleware can:manage-users garante que apenas superusuários possam acessar as rotas de gerenciamento de usuários.
Route::middleware(['auth', 'can:manage-users'])->group(function () {
    Route::resource('users', UserController::class);
});

// A middleware can:manage-companies garante que apenas superusuários possam acessar as rotas de gerenciamento de empresas.
Route::resource('companies', CompanyController::class)->middleware('can:manage-companies');
Route::resource('bank-accounts', BankAccountController::class);

Route::middleware(['auth'])->group(function () {
    Route::resource('bank-accounts.transactions', TransactionController::class)->shallow();
});

// Testes para requisições a API de livre acesso.
// Não faz uso de chave (token) de segurança, nem certificado digital.
Route::get('api/get', [HomeController::class, 'getApi']);
Route::get('api/post', [HomeController::class, 'postApi']);

// exemplos de como consumir a API de dados do Portal da Transparência do Governo Federal.
// faz uso de chave (token) de segurança no HEADER.
Route::get('api-de-dados/imoveis', [TransparenciaController::class, 'imoveis']);
Route::get('api-de-dados/bpc', [TransparenciaController::class, 'bpc']);

// PagBank
Route::get('pagbank/extrato', [PagbankController::class, 'getExtrato'])->name('extrato');
Route::get('pagbank/token', [PagbankController::class, 'token']);

// teste de requisições a API do Banco do Santander.
Route::get('/santander/token', [SantanderController::class, 'getToken']);
Route::get('/santander/saldo', [SantanderController::class, 'getSaldo']);
Route::get('/santander/contas', [SantanderController::class, 'getContas'])->name('santander.contas');
Route::get('/santander/extrato', [SantanderController::class, 'getExtrato'])->name('santander.extrato');

require __DIR__ . '/auth.php';
