<?php

use App\Http\Controllers\Programas\ActividadesController;
use App\Http\Controllers\Programas\CursosController;
use App\Http\Controllers\Programas\DetallesActividadesController;
use App\Http\Controllers\Programas\DetallesCursosController;
use App\Http\Controllers\Programas\HorariosController;
use App\Http\Controllers\Programas\InscripcionesActividadesController;
use App\Http\Controllers\Programas\InscripcionesCursosController;
use App\Http\Controllers\Programas\InscripcionesModulosController;
use App\Http\Controllers\Programas\InstructoresController;
use App\Http\Controllers\Programas\ModulosController;
use App\Http\Controllers\Programas\ProgramasController;
use App\Http\Controllers\Programas\RequisitosController;
use App\Http\Controllers\Programas\SedesController;
use App\Models\adm_gds\estados_actividades;
use App\Models\adm_gds\temporalidades;
use App\Models\adm_gds\tipos_actividades;
use Illuminate\Support\Facades\Route;


Route::get('temporalidades',function(){
    return response(temporalidades::all());
});

Route::get('programas/get-actividades/{programa_id}/{year}',[ProgramasController::class,'get_actividades']);
Route::get('programas/get-cursos/{programa_id}',[ProgramasController::class,'get_cursos']);
Route::get('programas/get-beneficiarios/{programa_id}/{year}',[ProgramasController::class,'get_beneficiarios']);
Route::post('programas/store-cursos',[ProgramasController::class,'store_cursos']);
Route::post('programas/store-actividades',[ProgramasController::class,'store_actividades']);
Route::apiResource('programas',ProgramasController::class);

Route::get('modulos/get-cursos/{modulo_id}',[ModulosController::class,'get_cursos']);
Route::post('modulos/store-cursos',[ModulosController::class,'store_cursos']);
Route::post('modulos/asignar-requisitos/{modulo}',[ModulosController::class,'assign_requirements']);
Route::apiResource('modulos',ModulosController::class);

Route::apiResource('cursos',CursosController::class);

Route::get('detalles-curso/get-requisitos/{curso}',[DetallesCursosController::class,'getRequirements']);
Route::post('detalles-curso/disabled/{curso}',[DetallesCursosController::class,'disabled']);
Route::post('detalles-curso/asignar-requisitos/{curso}',[DetallesCursosController::class,'assigRequirements']);
Route::apiResource('detalles-curso',DetallesCursosController::class)->parameters(['detalles-curso' => 'curso']);

Route::apiResource('instructores',InstructoresController::class)->parameters(['instructores' => 'instructor']);
Route::apiResource('horarios',HorariosController::class);
Route::apiResource('sedes',SedesController::class);

Route::apiResource('actividades',ActividadesController::class)->parameters(['actividades' => 'actividad']);
Route::post('detalles-actividades/disabled/{actividad}',[DetallesActividadesController::class,'disabled']);
Route::apiResource('detalles-actividades',DetallesActividadesController::class)->parameters(['detalles-actividades' => 'actividad']);

Route::get('inscripciones-curso/get-beneficiarios/{detalle_curso_id}/{year}',[InscripcionesCursosController::class,'get_beneficiarios']);
Route::post('inscripciones-curso/store-beneficiarios',[InscripcionesCursosController::class,'store_beneficiarios']);
Route::apiResource('inscripciones-curso',InscripcionesCursosController::class)->parameters(['inscripciones-curso' => 'inscripcion']);

Route::get('inscripciones-modulo/get-beneficiarios/{modulo_id}/{year}',[InscripcionesModulosController::class,'get_beneficiarios']);
Route::post('inscripciones-modulo/store-beneficiarios',[InscripcionesModulosController::class,'store_beneficiarios']);
Route::apiResource('inscripciones-modulo',InscripcionesModulosController::class)->parameters(['inscripciones-modulo' => 'inscripcion']);

Route::get('inscripciones-actividad/get-beneficiarios/{detalle_actividad_id}/{year}',[InscripcionesActividadesController::class,'get_beneficiarios']);
Route::post('inscripciones-actividad/store-beneficiarios',[InscripcionesActividadesController::class,'store_beneficiarios']);
Route::apiResource('inscripciones-actividad',InscripcionesActividadesController::class)->parameters(['inscripciones-actividad' => 'inscripcion']);

Route::apiResource('requisitos',RequisitosController::class);


Route::get('tipos-actividades',function() {
    try {
        return response(tipos_actividades::all());
    } catch (\Throwable $th) {
        return response($th->getMessage());
    }
});

Route::get('estados-actividades',function() {
    try {
        return response(estados_actividades::all());
    } catch (\Throwable $th) {
        return response($th->getMessage());
    }
});

