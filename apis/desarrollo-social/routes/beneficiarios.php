<?php

use App\Http\Controllers\Beneficiarios\BeneficiariosController;
use Illuminate\Support\Facades\Route;

Route::get('beneficiarios/bitacora/{beneficiario}',[BeneficiariosController::class,'bitacora']);
Route::get('beneficiarios/historial',[BeneficiariosController::class,'historial']);
Route::post('beneficiarios/create',[BeneficiariosController::class,'create']);
Route::post('beneficiarios/estado/{beneficiario}',[BeneficiariosController::class,'changeStatus']);
Route::post('beneficiarios/consulta-back-up',[BeneficiariosController::class,'consultaBackUp']);
Route::post('beneficiarios/consulta-beneficiario-unico',[BeneficiariosController::class,'consultaBeneficiarioUnico']);
Route::apiResource('beneficiarios',BeneficiariosController::class);

