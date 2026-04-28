<?php

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
