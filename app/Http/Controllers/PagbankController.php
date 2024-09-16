<?php

namespace App\Http\Controllers;

use App\Services\Banks\PagBankService as BanksPagBankService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class PagbankController extends Controller
{
    protected mixed $pagbankService;

    // Método construtor: faz injeção de dependências,
    // disponibilizando uso no controller, dos métodos da Classe de Serviço.
    public function __construct(BanksPagBankService $PagbankService)
    {
        $this->pagbankService = $PagbankService;
    }

    // Método para checar validade do token do Pagbank.
    public function token(): JsonResponse
    {
        // Chama o método da Classe de Serviço responsável por checar a validade do token do Pagbank.
        $dados = $this->pagbankService->checkToken();

        return response()->json(['response' => $dados]);
    }

    // Método para buscar extrato do Pagbank.
    public function getExtrato(Request $request): View
    {
        // Define data inicial e data final a ser consultado, caso não seja informado, assume data padrão.
        $request->filled('type') ? $type                     = $request->input('type') : $type = 2;
        $request->filled('data_movimento') ? $data_movimento = $request->input('data_movimento') : $data_movimento = '2024-01-01';

        // Define a página a ser consultada, caso não seja informada, assume-se a primeira página.
        $request->filled('page') ? $page = $request->input('page') : $page = 1;

        // Chama o método da Classe de Serviço responsável por buscar o extrato do Pagbank.
        $transactions = $this->pagbankService->getExtrato($type, $data_movimento, $page);

        // Busca os tipos de transações para o formulário.
        $types = $this->getTipos();

        // Calcula o número de páginas para a listagem do extrato.
        $pages = $this->getPages($transactions);

        return view('pagbank', compact(['transactions', 'data_movimento', 'types', 'type', 'pages', 'page']));
    }

    // Método para preparar os tipos de transações em array.
    public function getTipos(): mixed
    {
        $data = [
            ['id' => 1, 'name' => 'Transacional'],
            ['id' => 2, 'name' => 'Financeiro'],
            ['id' => 3, 'name' => 'Antecipação'],
        ];

        return $data;
    }

    // Método para preparar as páginas do extrato em array.
    public function getPages(mixed $dados): mixed
    {
        $pages = [];

        for ($x = 1; $x <= $dados['pagination']['totalPages']; $x++) {
            array_push($pages, ['id' => $x, 'name' => 'Página ' . $x]);
        }

        return $pages;
    }
}
