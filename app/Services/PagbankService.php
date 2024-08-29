<?php

namespace App\Services;

use GuzzleHttp\Client;

class PagbankService
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

    // Busca Extrato
    // Obrigatório pagina=1.
    public function getExtrato(): mixed
    {
        $response = $this->client->get(
            $this->baseUrl . '?dataMovimento=2024-08-27&pageNumber=1&pageSize=10&tipoMovimento=2',
            [
                'headers' => [
                    'chave-api-dados' => env('TRANSPARENCIA_TOKEN'),
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }
}
