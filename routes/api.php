<?php

use App\Http\Controllers\PersonaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
route::get('retornarP',[PersonaController::class,'index']);
route::post('personas',[PersonaController::class,'store']);
Route::post('/buscarmedicos', [PersonaController::class, 'buscarMedicosPorEspecialidad']);
Route::post('/reservarcita', [PersonaController::class, 'reservarCita']);
Route::get('/mostrarcitas', [PersonaController::class, 'mostrarCitasReservadas']);
