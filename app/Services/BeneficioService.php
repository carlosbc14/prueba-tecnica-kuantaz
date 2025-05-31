<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class BeneficioService
{
    protected $baseUrl;
    protected $headers;

    public function __construct()
    {
        $this->baseUrl = env('API_URL');
        $this->headers = [
            'Accept' => 'application/json',
        ];
    }

    public function getAll(): Collection
    {
        $url = $this->baseUrl . '/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02';
        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->failed()) throw new \Exception('Error al consumir la API');

        return collect($response->json()['data']);
    }
}
