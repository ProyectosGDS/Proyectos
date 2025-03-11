<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('login',[LoginController::class,'authenticate']);
Route::post('logout',[LoginController::class,'logout'])->middleware(['jwtAuth']);
Route::post('me',[LoginController::class,'verifyAuth'])->middleware(['jwtAuth']);

Route::post('register',[RegisterController::class,'register']);