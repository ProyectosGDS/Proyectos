<?php

use App\Http\Controllers\PruebaTokenRenapController;
use Illuminate\Support\Facades\Route;






Route::get('token-renap-generated',[PruebaTokenRenapController::class,'tokenGenerate']);
Route::get('verify-cui',[PruebaTokenRenapController::class,'verifyCui']);