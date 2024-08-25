<?php

namespace App\Services;

use GuzzleHttp\Client;

class ApiService
{
    protected mixed $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getApi(): mixed
    {
        $response = $this->client->get("https://jsonplaceholder.typicode.com/posts/1");

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array<string, string> $dados
     */
    public function postApi(array $dados): mixed
    {
        $response = $this->client->post("https://jsonplaceholder.typicode.com/posts", [
            'json' => [
                'title'  => $dados['title'],
                'body'   => $dados['body'],
                'userId' => 5,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
