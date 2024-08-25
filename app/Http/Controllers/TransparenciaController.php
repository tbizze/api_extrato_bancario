<?php

namespace App\Http\Controllers;

use App\Services\TransparenciaService;
use Illuminate\Http\{JsonResponse};

/* FONTES:
https://portaldatransparencia.gov.br/pagina-interna/603579-api-de-dados-exemplos-de-uso
https://api.portaldatransparencia.gov.br/swagger-ui/index.html#/Benef%C3%ADcios/bpc
https://portaldatransparencia.gov.br/api-de-dados
https://portaldatransparencia.gov.br/api-de-dados/usuario/cadastro  <== meus dados da API

### Obrigatório variáveis de ambiente:
TRANSPARENCIA_BASE_URL=""
TRANSPARENCIA_TOKEN=""
 */

class TransparenciaController extends Controller
{
    private mixed $transparencia;

    public function __construct(TransparenciaService $TransparenciaService)
    {
        $this->transparencia = $TransparenciaService;
    }

    // Método traz imóveis.
    public function imoveis(): JsonResponse
    {
        $dados = $this->transparencia->getImoveis(2);

        return response()->json($dados);
    }

    // Método traz resumo do BPC por município. anoMes=AAAAMM.
    public function bpc(): JsonResponse
    {
        $dados = $this->transparencia->getBpc(1, '202301', '3549706');

        return response()->json($dados);
    }
}
