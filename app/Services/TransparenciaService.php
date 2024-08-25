<?php

namespace App\Services;

use GuzzleHttp\Client;

class TransparenciaService
{
    protected mixed $client;

    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->client  = new Client();
        $this->baseUrl = env('TRANSPARENCIA_BASE_URL');
        $this->token   = env('TRANSPARENCIA_TOKEN');
    }

    // Busca Imóveis
    // Obrigatório pagina=1.
    public function getImoveis(int $pagina): mixed
    {
        $response = $this->client->get(
            $this->baseUrl . '/api-de-dados/imoveis?pagina=' . $pagina,
            [
                'headers' => [
                    'chave-api-dados' => env('TRANSPARENCIA_TOKEN'),
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    // Busca BPC por município
    // Obrigatório pagina=1 mesAno=202301 codigoIbge=3549706
    public function getBpc(int $pagina, string $anoMes, string $codigoIbge): mixed
    {
        $response = $this->client->get(
            $this->baseUrl . '/api-de-dados/bpc-por-municipio?' . 'pagina=' . $pagina . '&mesAno=' . $anoMes . '&codigoIbge=' . $codigoIbge,
            [
                'headers' => [
                    'chave-api-dados' => $this->token,
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
