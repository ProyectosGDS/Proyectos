<?php

use App\Http\Controllers\Eventos\EstatusEventoController;
use App\Http\Controllers\Eventos\EventosController;
use App\Http\Controllers\Eventos\TipoEventoController;
use Illuminate\Support\Facades\Route;

Route::put('eventos/cambiar-status-evento/{evento}',[EventosController::class,'changeStatus']);
Route::apiResource('eventos',EventosController::class)->except(['destroy']);

Route::get('tipos-eventos',[TipoEventoController::class,'index']);
Route::get('estatus-eventos',[EstatusEventoController::class,'index']);