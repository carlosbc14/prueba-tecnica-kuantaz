<?php

namespace App\Http\Controllers;

use App\Services\BeneficioService;
use App\Services\FichaService;
use App\Services\FiltroService;
use Illuminate\Http\JsonResponse;

class BeneficioController extends Controller
{
    public function __construct(
        protected BeneficioService $beneficioService,
        protected FiltroService $filtroService,
        protected FichaService $fichaService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $beneficios = $this->beneficioService->getAll();
            $filtros = $this->filtroService->getAll()->keyBy('id_programa');
            $fichas = $this->fichaService->getAll()->keyBy('id');
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        $data = $beneficios
            ->filter(function ($beneficio) use ($filtros) {
                $filtro = $filtros[$beneficio['id_programa']] ?? null;
                return $filtro &&
                    $beneficio['monto'] >= $filtro['min'] &&
                    $beneficio['monto'] <= $filtro['max'];
            })
            ->map(function ($beneficio) use ($filtros, $fichas) {
                $filtro = $filtros[$beneficio['id_programa']] ?? null;
                $ficha = isset($filtro['ficha_id']) ? $fichas[$filtro['ficha_id']] ?? null : null;
                return [
                    ...$beneficio,
                    'ficha' => $ficha ?? [],
                ];
            })
            ->groupBy(fn($beneficio) => date('Y', strtotime($beneficio['fecha'])))
            ->sortKeysDesc()
            ->map(fn($grupo, $anio) => [
                'anio' => (int) $anio,
                'cantidad_total' => $grupo->count(),
                'monto_total' => $grupo->sum('monto'),
                'beneficios' => $grupo->values(),
            ])
            ->values();

        return response()->json([
            'code' => 200,
            'success' => true,
            'data' => $data,
        ]);
    }
}
