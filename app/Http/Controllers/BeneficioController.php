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

    /**
     * @OA\Get(
     *     path="/api/beneficios-procesados",
     *     summary="Obtener lista de beneficios agrupados por año",
     *     tags={"Beneficios"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de beneficios agrupados por año",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="anio", type="integer", example=2024),
     *                     @OA\Property(property="cantidad_total", type="integer", example=10),
     *                     @OA\Property(property="monto_total", type="number", format="integer", example=15000),
     *                     @OA\Property(
     *                         property="beneficios",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id_programa", type="integer", example=1),
     *                             @OA\Property(property="monto", type="number", format="integer", example=1200),
     *                             @OA\Property(property="fecha_recepcion", type="string", example="31/05/2024"),
     *                             @OA\Property(property="fecha", type="string", format="date", example="2024-05-31"),
     *                             @OA\Property(
     *                                 property="ficha",
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=10),
     *                                 @OA\Property(property="nombre", type="string", example="Nombre de Ficha"),
     *                                 @OA\Property(property="id_programa", type="integer", example=1),
     *                                 @OA\Property(property="url", type="string", example="nombre-ficha"),
     *                                 @OA\Property(property="categoria", type="string", example="Categoría de ficha"),
     *                                 @OA\Property(property="descripcion", type="string", example="Descripción de ficha")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
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
