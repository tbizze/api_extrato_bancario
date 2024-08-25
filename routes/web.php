<?php

use App\Http\Controllers\{HomeController, ProfileController};
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

require __DIR__ . '/auth.php';
