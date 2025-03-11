<?php

use App\Http\Controllers\ParticipacionCiudadana\CursosController;
use App\Http\Controllers\ParticipacionCiudadana\EventosController;
use App\Http\Controllers\ParticipacionCiudadana\InscripcionController;
use Illuminate\Support\Facades\Route;


Route::get('participacion-ciudadana/catalogos',[InscripcionController::class,'catalogos']);
Route::get('participacion-ciudadana/eventos',[EventosController::class,'index']);
Route::get('participacion-ciudadana',[CursosController::class,'index']);
Route::get('participacion-ciudadana/{clase}',[CursosController::class,'show']);

Route::post('participacion-ciudadana/inscripcion',[InscripcionController::class,'store']);