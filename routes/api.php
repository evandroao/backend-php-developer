<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EpisodeController;
use App\Http\Controllers\ShowController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem autenticação)
|--------------------------------------------------------------------------
|
| BUG INTENCIONAL: O endpoint de login está exigindo autenticação JWT.
| O candidato deve mover esta rota para fora do middleware 'jwt.auth'.
|
*/

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

/*
|--------------------------------------------------------------------------
| Rotas protegidas (requerem JWT)
|--------------------------------------------------------------------------
|
| BUG INTENCIONAL: As rotas de /api/users não possuem verificação de role.
| No projeto original Java, list/update/delete exigem ADMIN.
| O candidato deve adicionar o middleware de role adequado.
|
*/
Route::middleware(['jwt.auth', 'role:ADMIN'])->group(function () {
    // Users - CRUD
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Shows - apenas ADMIN pode sincronizar
    Route::post('/shows', [ShowController::class, 'store'])->middleware('throttle:sync');
});

Route::middleware(['jwt.auth'])->group(function () {
    // Shows - leitura liberada para ADMIN e USER
    Route::get('/shows', [ShowController::class, 'index']);
    Route::get('/shows/{id}', [ShowController::class, 'show']);

    // Episodes - leitura liberada para ADMIN e USER
    Route::get('/episodes/average', [EpisodeController::class, 'average']);
});
