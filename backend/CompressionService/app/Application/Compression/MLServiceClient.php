<?php

namespace App\Application\Compression;

use GuzzleHttp\Client;

class MLServiceClient
{
    private Client $client;
    private string $endpoint;

    public function __construct()
    {
        $host = env('ML_SERVICE_HOST', 'mlservice'); // имя контейнера ML
        $port = env('ML_SERVICE_PORT', '5000');      // порт FastAPI
        $this->endpoint = env('ML_SERVICE_ENDPOINT', '/classify');

        $this->client = new Client([
            'base_uri' => "http://{$host}:{$port}",
            'timeout'  => 10,
        ]);
    }

    public function classify(string $filePath): array
    {
        $response = $this->client->post($this->endpoint, [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath)
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
