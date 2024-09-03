<?php

namespace App\Http\Controllers;

use App\Services\SantanderService;
use Illuminate\Http\{JsonResponse};

class SantanderController extends Controller
{
    protected mixed $santanderService;

    public function __construct(SantanderService $santanderService)
    {
        $this->santanderService = $santanderService;
    }

    // Método para obter TOKEN DE ACESSO via autenticação OAuth.
    public function getToken(): JsonResponse
    {
        $token = $this->santanderService->generateAccessToken();
        //$token = $this->santanderService->getAccessToken();

        return response()->json(['access_token' => $token]);
    }

    // Método para requisição do SALDO à API Saldo e Extrato do Santander.
    public function getSaldo(): JsonResponse
    {
        $balance = $this->santanderService->getAccountSaldo();

        return response()->json($balance);
    }

    // Método para requisição do Extrato de Movimentações à API Saldo e Extrato do Santander.
    public function getExtrato(): JsonResponse
    {
        $balance = $this->santanderService->getAccountExtrato();

        return response()->json($balance);
    }

    // Método para requisição da Listagem de Contas à API Saldo e Extrato do Santander.
    public function getContas(): JsonResponse
    {
        $balance = $this->santanderService->getAccountsList();

        return response()->json($balance);
    }
}
