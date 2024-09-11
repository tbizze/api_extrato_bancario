<?php

namespace App\Http\Controllers;

use App\Services\Banks\SantanderService as BanksSantanderService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class SantanderController extends Controller
{
    protected mixed $santanderService;

    public function __construct(BanksSantanderService $santanderService)
    {
        $this->santanderService = $santanderService;
    }

    // Método para obter TOKEN DE ACESSO via autenticação OAuth.
    public function getToken(): JsonResponse
    {
        $token = $this->santanderService->generateAccessToken();

        return response()->json(['access_token' => $token]);
    }

    // Método para requisição do SALDO à API Saldo e Extrato do Santander.
    public function getSaldo(): View
    {
        // Chama a função de requisição do saldo.
        $saldo = $this->santanderService->getAccountSaldo();

        // return response()->json($balance);
        return view('santander-saldo', compact(['saldo']));
    }

    // Método para requisição do Extrato de Movimentações à API Saldo e Extrato do Santander.
    public function getExtrato(Request $request): View
    {
        // Define data inicial e data final a ser consultado, caso não seja informado, assume data padrão.
        $request->filled('initial_date') ? $initial_date = $request->input('initial_date') : $initial_date = '2024-08-01';
        $request->filled('finalDate') ? $finalDate       = $request->input('finalDate') : $finalDate = '2024-08-30';

        // Define a página a ser consultada, caso não seja informada, assume-se a primeira página.
        $request->filled('page') ? $page = $request->input('page') : $page = 1;

        // Chama a função de requisição do extrato de movimentações.
        $transactions = $this->santanderService->getAccountExtrato($initial_date, $finalDate, $page);

        // Calcula o número de páginas para a listagem do extrato.
        $pages = $this->getPages($transactions);

        return view('santander', compact(['transactions', 'initial_date', 'finalDate', 'page', 'pages']));
    }

    // Método para requisição da Listagem de Contas à API Saldo e Extrato do Santander.
    public function getContas(Request $request): View
    {
        // Define a página a ser consultada, caso não seja informada, assume-se a primeira página.
        $request->filled('page') ? $page = $request->input('page') : $page = 1;

        // Chama a função de requisição da lista de contas.
        $contas = $this->santanderService->getAccountsList($page);

        // Calcula o número de páginas para a listagem do extrato.
        $pages = $this->getPages($contas);

        return view('santander-contas', compact(['contas', 'page', 'pages']));
    }

    // Método para preparar as páginas do extrato em array.
    public function getPages(mixed $dados): mixed
    {
        $pages = [];

        for ($x = 1; $x <= $dados['_pageable']['totalPages']; $x++) {
            array_push($pages, ['id' => $x, 'name' => 'Página ' . $x]);
        }

        return $pages;
    }
}
