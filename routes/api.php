<?php

use App\Http\Controllers\BeneficioController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn() => response()->json([
    'description' => 'Prueba Técnica Kuantaz',
    'version' => '1.0.0',
]));

Route::get('/beneficios-procesados', [BeneficioController::class, 'index']);
