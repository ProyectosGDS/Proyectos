<?php

use App\Http\Controllers\Globales\BitacoraController;
use App\Http\Controllers\Globales\CatalogosController;
use App\Http\Controllers\Globales\DireccionesController;
use App\Http\Controllers\Globales\ExportController;
use App\Models\adm_gds\distritos;
use Illuminate\Support\Facades\Route;

Route::get('departamentos',[DireccionesController::class,'departamentos']);
Route::get('municipios-departamento/{departamento_id}',[DireccionesController::class,'municipios_departamento']);
Route::get('zonas',[DireccionesController::class,'zonas']);
Route::get('grupos-habitacionales',[DireccionesController::class,'grupos_habitacionales']);
Route::get('grupos-zonas/{zona_id}/{grupo_habitacional_id}',[DireccionesController::class,'grupos_zonas']);

Route::get('etnias',[CatalogosController::class,'etnias']);
Route::get('escolaridades',[CatalogosController::class,'escolaridades']);
Route::get('estados-civiles',[CatalogosController::class,'estados_civiles']);
Route::get('tipos-sangre',[CatalogosController::class,'tipos_sangre']);
Route::get('parentescos',[CatalogosController::class,'parentescos']);

Route::get('catalogos-curso',[CatalogosController::class,'catalogosCurso'])->middleware(['jwtAuth']);
Route::get('catalogos',[CatalogosController::class,'catalogosBeneficiario']);
Route::get('catalogos-actividad',[CatalogosController::class,'catalogosActividad'])->middleware(['jwtAuth']);

Route::get('distritos',function(){
    return response(distritos::all());
});

Route::apiResource('bitacora',BitacoraController::class)->middleware(['jwtAuth']);

Route::post('exportar-excel',[ExportController::class,'exportExcel'])->middleware(['jwtAuth']);