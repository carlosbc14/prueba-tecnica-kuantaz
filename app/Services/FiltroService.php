<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class FiltroService
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
        $url = $this->baseUrl . '/b0ddc735-cfc9-410e-9365-137e04e33fcf';
        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->failed()) throw new \Exception('Error al consumir la API');

        return collect($response->json()['data']);
    }
}
