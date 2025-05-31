<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class FichaService
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
        $url = $this->baseUrl . '/4654cafa-58d8-4846-9256-79841b29a687';
        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->failed()) throw new \Exception('Error al consumir la API');

        return collect($response->json()['data']);
    }
}
