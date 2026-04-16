<?php

use App\Http\Controllers\Programas\TrasladosController;
use Illuminate\Support\Facades\Route;

Route::post('busqueda-beneficiarios', [TrasladosController::class,'busquedaBeneficiarios']);
Route::post('validar-recurso-actual', [TrasladosController::class,'validarRecursoActual']);
Route::post('validar-recurso-nuevo', [TrasladosController::class,'validarRecursoNuevo']);
Route::post('realizar-traslado', [TrasladosController::class,'realizarTraslado']);