<?php

use App\Http\Controllers\{HomeController, ProfileController, SantanderController, TransparenciaController};
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

// Testes para requisições a API de livre acesso.
// Não faz uso de chave (token) de segurança, nem certificado digital.
Route::get('api/get', [HomeController::class, 'getApi']);
Route::get('api/post', [HomeController::class, 'postApi']);

// exemplos de como consumir a API de dados do Portal da Transparência do Governo Federal.
// faz uso de chave (token) de segurança no HEADER.
Route::get('api-de-dados/imoveis', [TransparenciaController::class, 'imoveis']);
Route::get('api-de-dados/bpc', [TransparenciaController::class, 'bpc']);

// teste de requisições a API do Banco do Santander.
Route::get('/santander/token', [SantanderController::class, 'getToken']);
Route::get('/santander/contas', [SantanderController::class, 'getContas']);
Route::get('/santander/saldo', [SantanderController::class, 'getSaldo']);
Route::get('/santander/extrato', [SantanderController::class, 'getExtrato']);

require __DIR__ . '/auth.php';
