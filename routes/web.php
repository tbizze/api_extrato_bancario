<?php

use App\Http\Controllers\{BankAccountController, CompanyController, CredentialBankAccountController, HomeController, PagbankController, ProfileController, SantanderController, TransactionController, TransactionImportController, TransparenciaController, UserController};
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

// Rotas protegidas por autenticação e por gates personalizados.
Route::middleware(['auth'])->group(function () {
    // A middleware can:manage-users garante que apenas superusuários possam acessar as rotas.
    Route::resource('users', UserController::class)->middleware('can:manage-users');

    // A middleware can:manage-companies garante que apenas superusuários possam acessar as rotas.
    Route::resource('companies', CompanyController::class)->middleware('can:manage-companies');

    // Gerenciamento de credenciais das APIs dos bancos.
    // A middleware can:manage-companies garante que apenas superusuários possam acessar as rotas.
    Route::prefix('bank-accounts/{bank_account}/credentials')->group(function () {
        Route::get('/edit', [CredentialBankAccountController::class, 'edit'])
            ->middleware('can:manage-companies')->name('bank-accounts.credentials.edit');
        Route::put('/', [CredentialBankAccountController::class, 'update'])
            ->middleware('can:manage-companies')->name('bank-accounts.credentials.update');
        // ... outras rotas relacionadas a artigos
    });
});

// Rotas protegidas por autenticação.
Route::middleware(['auth'])->group(function () {
    // Contas bancárias --> resource completo.
    Route::resource('bank-accounts', BankAccountController::class);

    // Transações -> resource parcial.
    Route::resource('bank-accounts.transactions', TransactionController::class)->shallow();
    // View para parâmetros da importação de transações.
    Route::get('bank-accounts/{bank_account}/transactions/select', [TransactionImportController::class, 'select'])
        ->name('bank-accounts.transactions.select');
    // Importação de transações.
    Route::post('bank-accounts/{bank_account}/transactions/import', [TransactionImportController::class, 'import'])
        ->name('bank-accounts.transactions.import');
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
