<?php

use App\Http\Controllers\Programas\ControlAsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('control-asistencia/beneficiarios-curso', [ControlAsistenciaController::class,'getBeneficiariosCurso']);
Route::get('control-asistencia/beneficiarios-modulo', [ControlAsistenciaController::class,'getBeneficiariosModulo']);
Route::get('control-asistencia/listado-asistencia',[ControlAsistenciaController::class,'listadoAsistencia']);
Route::post('control-asistencia/registrar/{curso_modulo_id}', [ControlAsistenciaController::class,'registrarAsistencias']);





