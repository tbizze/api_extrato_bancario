<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

use App\Services\ApiService;
use Illuminate\Http\{JsonResponse};

class HomeController extends Controller
{
    // Classe para tratar do Bradesco.
    protected mixed $apiTest;

    // Método construtor: faz injeção de dependências,
    // disponibilizando uso no controller, dos métodos da Classe de Serviço.
    public function __construct(ApiService $ApiTestService)
    {
        $this->apiTest = $ApiTestService;
    }

    // Método simples de consulta. Sem autenticação.
    public function getApi(): JsonResponse
    {
        $dados = $this->apiTest->getApi();
        // dd($dados);
        // foreach ($dados as $dado) {
        //     echo $extrato['id'];
        // }

        return response()->json(['message' => 'Dados obtidos com sucesso!', $dados]);
    }

    // Método simples de envio. Sem autenticação.
    public function postApi(): JsonResponse
    {
        $form_enviar = [
            'title' => 'Título',
            'body'  => 'Teste de mensagem de APIs.',
        ];
        $dados = $this->apiTest->postApi($form_enviar);
        // dd($dados);
        // foreach ($dados as $dado) {
        //     echo $extrato['id'];
        // }

        return response()->json(['message' => 'Dados postados com sucesso!', $dados]);
    }
}
