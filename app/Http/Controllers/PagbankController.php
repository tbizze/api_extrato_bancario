<?php

namespace App\Http\Controllers;

use App\Services\PagbankService;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\View\View;

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
    public function getExtrato(Request $request): View
    {
        if ($request->filled('date')) {
            $date = $request->date;
        } else {
            $date = '2024-01-01';
        }

        if ($request->filled('type')) {
            $tipo = $request->type;
        } else {
            $tipo = 1;
        }

        $tipos = $this->getTipos();

        $dados = $this->pagbankService->getExtrato($tipo, $date);
        //dd($dados['detalhes']);

        return view('pagbank', compact(['dados', 'date', 'tipos', 'tipo']));

        //return response()->json(['response' => $dados]);
        //return response()->json(['message' => 'Extrato obtido com sucesso!']);
    }

    public function getTipos(): mixed
    {
        $data = [
            ['id' => 1, 'name' => 'Transacional'],
            ['id' => 2, 'name' => 'Financeiro'],
            ['id' => 3, 'name' => 'Antecipação'],
        ];

        return $data;

        //$collection = collect($data);
        //return response()->json($collection);
    }
}

/*
array:3 [▼ // app\Http\Controllers\PagbankController.php:37
  "detalhes" => []
  "saldos" => []
  "pagination" => array:4 [▼
    "elements" => 10
    "totalPages" => 0
    "page" => 1
    "totalElements" => 0
  ]
] */
