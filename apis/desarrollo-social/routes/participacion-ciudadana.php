<?php

use App\Http\Controllers\ParticipacionCiudadana\CarouselController;
use App\Http\Controllers\ParticipacionCiudadana\CursosController;
use App\Http\Controllers\ParticipacionCiudadana\EventosController;
use App\Http\Controllers\ParticipacionCiudadana\InscripcionController;
use Illuminate\Support\Facades\Route;


Route::get('participacion-ciudadana/eventos',[EventosController::class,'index']);
Route::get('participacion-ciudadana',[CursosController::class,'index']);
Route::get('participacion-ciudadana/curso/{curso}',[CursosController::class,'getCurso']);
Route::get('participacion-ciudadana/modulo/{modulo}',[CursosController::class,'getModulo']);

Route::get('participacion-ciudadana/catalogos',[InscripcionController::class,'catalogos']);
Route::post('participacion-ciudadana/inscripcion',[InscripcionController::class,'store']);

Route::get('carrusel-imagenes',[CarouselController::class,'index']);