<?php

use App\Http\Controllers\Programas\CargosController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('cargos/generar-partidas/{programa}',[CargosController::class,'generar_partidas']);
