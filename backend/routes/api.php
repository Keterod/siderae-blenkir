<?php

use App\Http\Controllers\Api\AsistenciaController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\NotaController;
use App\Http\Controllers\Api\ProcesarRiesgoController;
use App\Http\Controllers\Api\VariableSocioeconomicaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'SIDERAE Backend',
    ]);
});

Route::middleware(['auth:sanctum'])->get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'usuario' => $user,
        'roles' => $user->getRoleNames()->values(),
        'permisos' => $user->getAllPermissions()->pluck('name')->values(),
    ]);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'permission:ver_dashboard'])->get('/dashboard', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Acceso autorizado a dashboard',
    ]);
});

Route::middleware(['auth:sanctum', 'permission:gestionar_estudiantes'])
    ->apiResource('estudiantes', EstudianteController::class)
    ->only(['index', 'store', 'show', 'update']);

Route::middleware(['auth:sanctum', 'permission:registrar_datos_academicos'])
    ->group(function (): void {
        Route::get('estudiantes/{estudiante}/notas', [NotaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/notas', [NotaController::class, 'store']);

        Route::get('estudiantes/{estudiante}/asistencias', [AsistenciaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/asistencias', [AsistenciaController::class, 'store']);

        Route::get('estudiantes/{estudiante}/variables-socioeconomicas', [VariableSocioeconomicaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/variables-socioeconomicas', [VariableSocioeconomicaController::class, 'store']);
    });

Route::middleware(['auth:sanctum', 'permission:procesar_riesgo'])
    ->post('estudiantes/{estudiante}/procesar-riesgo', [ProcesarRiesgoController::class, 'store']);
