<?php

use Illuminate\Support\Facades\Http;

test('devuelve los beneficios agrupados por año correctamente', function () {
    $baseUrl = env('API_URL');

    Http::fake([
        $baseUrl . '/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 101, 'monto' => 1500, 'fecha_recepcion' => '01/01/2023', 'fecha' => '2023-01-01'],
                ['id_programa' => 102, 'monto' => 1000, 'fecha_recepcion' => '01/05/2022', 'fecha' => '2022-05-01'],
                ['id_programa' => 101, 'monto' => 3000, 'fecha_recepcion' => '01/07/2023', 'fecha' => '2023-07-01'],
            ],
        ]),
        $baseUrl . '/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 101, 'tramite' => 'Trámite A', 'min' => 1000, 'max' => 5000, 'ficha_id' => 10],
                ['id_programa' => 102, 'tramite' => 'Trámite B', 'min' => 2000, 'max' => 3000, 'ficha_id' => 20],
            ],
        ]),
        $baseUrl . '/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id' => 10, 'nombre' => 'Ficha A', 'id_programa' => 101, 'url' => 'ficha_a', 'categoria' => 'Categoria A', 'descripcion' => 'Descripción A'],
                ['id' => 20, 'nombre' => 'Ficha B', 'id_programa' => 102, 'url' => 'ficha_b', 'categoria' => 'Categoria B', 'descripcion' => 'Descripción B'],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertOk()
        ->assertJsonStructure([
            'code',
            'success',
            'data' => [
                '*' => [
                    'anio',
                    'cantidad_total',
                    'monto_total',
                    'beneficios' => [
                        '*' => [
                            'id_programa',
                            'monto',
                            'fecha_recepcion',
                            'fecha',
                            'ficha' => ['id', 'nombre', 'id_programa', 'url', 'categoria', 'descripcion'],
                        ]
                    ]
                ]
            ]
        ])
        ->assertJsonFragment([
            'anio' => 2023,
            'cantidad_total' => 2,
            'monto_total' => 4500,
        ])
        ->assertJsonMissing([
            'anio' => 2022,
        ]);
});

test('devuelve error si falla la obtención de beneficios', function () {
    Http::fake([
        env('API_URL') . '/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response(['code' => 500, 'success' => false, 'message' => []], 500),
        '*' => Http::response(['code' => 200, 'success' => true, 'data' => []]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertStatus(500)
        ->assertJson([
            'code' => 500,
            'success' => false,
            'message' => 'Error al consumir la API',
        ]);
});

test('devuelve error si falla la obtención de filtros', function () {
    Http::fake([
        env('API_URL') . '/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response(['code' => 500, 'success' => false, 'message' => []], 500),
        '*' => Http::response(['code' => 200, 'success' => true, 'data' => []]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertStatus(500)
        ->assertJson([
            'code' => 500,
            'success' => false,
            'message' => 'Error al consumir la API',
        ]);
});

test('devuelve error si falla la obtención de fichas', function () {
    Http::fake([
        env('API_URL') . '/4654cafa-58d8-4846-9256-79841b29a687' => Http::response(['code' => 500, 'success' => false, 'message' => []], 500),
        '*' => Http::response(['code' => 200, 'success' => true, 'data' => []]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertStatus(500)
        ->assertJson([
            'code' => 500,
            'success' => false,
            'message' => 'Error al consumir la API',
        ]);
});

test('devuelve ficha vacía si no se encuentra en el listado', function () {
    $baseUrl = env('API_URL');

    Http::fake([
        $baseUrl . '/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 101, 'monto' => 1500, 'fecha_recepcion' => '01/01/2023', 'fecha' => '2023-01-01'],
            ],
        ]),
        $baseUrl . '/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 101, 'tramite' => 'Trámite', 'min' => 1000, 'max' => 2000, 'ficha_id' => 99],
            ],
        ]),
        $baseUrl . '/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id' => 10, 'nombre' => 'Ficha', 'id_programa' => 101, 'url' => 'ficha_a', 'categoria' => 'Categoria', 'descripcion' => 'Descripción'],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertOk()
        ->assertJsonPath('data.0.beneficios.0.ficha', []);
});

test('descarta beneficios sin filtro asociado', function () {
    $baseUrl = env('API_URL');

    Http::fake([
        $baseUrl . '/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 99, 'monto' => 1000, 'fecha_recepcion' => '01/01/2023', 'fecha' => '2023-01-01'],
                ['id_programa' => 101, 'monto' => 1500, 'fecha_recepcion' => '04/02/2023', 'fecha' => '2023-02-04'],
            ],
        ]),
        $baseUrl . '/b0ddc735-cfc9-410e-9365-137e04e33fcf' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id_programa' => 101, 'tramite' => 'Trámite', 'min' => 1000, 'max' => 2000, 'ficha_id' => 10],
            ],
        ]),
        $baseUrl . '/4654cafa-58d8-4846-9256-79841b29a687' => Http::response([
            'code' => 200,
            'success' => true,
            'data' => [
                ['id' => 10, 'nombre' => 'Ficha', 'id_programa' => 101, 'url' => 'ficha_a', 'categoria' => 'Categoria', 'descripcion' => 'Descripción'],
            ],
        ]),
    ]);

    $response = $this->getJson('/api/beneficios-procesados');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonCount(1, 'data.0.beneficios')
        ->assertJsonMissing(['id_programa' => 99]);
});
