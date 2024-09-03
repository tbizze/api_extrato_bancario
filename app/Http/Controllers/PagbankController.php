<?php

namespace App\Http\Controllers;

use App\Services\PagbankService;
use Illuminate\Http\JsonResponse;

class PagbankController extends Controller
{
    // Classe para tratar do Bradesco.
    protected mixed $pagbankService;

    // Método construtor: faz injeção de dependências,
    // disponibilizando uso no controller, dos métodos da Classe de Serviço.
    public function __construct(PagbankService $PagbankService)
    {
        $this->pagbankService = $PagbankService;
    }

    // Implementação do método para checar validade do token do Pagbank
    public function token(): JsonResponse
    {
        $dados = $this->pagbankService->checkToken();

        return response()->json(['response' => $dados]);
    }

    // Implementação do método para buscar extrato do Pagbank
    public function getExtrato(): JsonResponse
    {
        $dados = $this->pagbankService->getExtrato();

        return response()->json(['response' => $dados]);
        //return response()->json(['message' => 'Extrato obtido com sucesso!']);
    }
}
