<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PagbankService
{
    protected mixed $client;

    protected string $baseUrl;

    protected string $clientId;

    protected string $token;

    // Método construtor do client.
    // Ao instanciar, deve enviar o token através da chave 'authorization' no headers.
    public function __construct()
    {
        $this->baseUrl  = env('PAGBANK_BASE_URI');
        $this->clientId = env('PAGBANK_CLIENT_ID');
        $this->token    = env('PAGBANK_TOKEN');

        $this->client = new Client();
    }

    // Método para validar o token.
    // Deve enviar via parâmetros de URL o clientId e o token.
    public function checkToken(): mixed
    {
        try {
            $response = $this->client->GET($this->baseUrl . '/users/' . $this->clientId . '/token/' . $this->token, [
                'headers' => [
                    'accept'        => 'application/json',
                    'authorization' => 'Bearer ' . $this->token, // Anexa o token recebido no credenciamento.
                ],
            ]);

            $data         = json_decode($response->getBody(), true);
            $codeResponse = $data['code'];

            if ($codeResponse != 200) {
                /* Retorna um JSON com:
                   'code' => 404
                   'codeValue' => NOT FOUND
                   'message' => 'Os parâmetros informados não são válidos.'
                 */
                return response()->json(['error' => 'Token inválido.', 'message' => $data['code'] . ' ' . $data['codeValue'] . ' -> ' . $data['message']], 400);
            }

            /* Retorna um JSON com:
                'code' => 404
                'codeValue' => NOT FOUND
                'message' => 'Os parâmetros informados não são válidos.'
            */
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()], 400);
        }
    }

    // Busca Extrato
    public function getExtrato(int $tipo, string $data): mixed
    {
        $credentials   = base64_encode("$this->clientId:$this->token");
        $tipoExtrato   = 1;
        $dataMovimento = '2024-08-26';

        try {
            $response = $this->client->GET($this->baseUrl . "/2.01/movimentos?tipoMovimento=$tipo&dataMovimento=$data&pageNumber=1&pageSize=20", [
                'headers' => [
                    'Authorization' => "Basic $credentials",
                ],
            ]);

            //dd(json_decode($response->getBody(), true));
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Registre ou trate o erro, conforme necessário.
            return response()->json(['error' => 'Falha na requisição.', 'message' => $e->getMessage()]);
        }
    }
}
