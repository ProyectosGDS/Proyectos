<?php

use App\Http\Controllers\Admin\DependenciasController;
use App\Http\Controllers\Admin\MenusController;
use App\Http\Controllers\Admin\PaginasController;
use App\Http\Controllers\Admin\PerfilesController;
use App\Http\Controllers\Admin\PermisosController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsuariosController;
use Illuminate\Support\Facades\Route;


Route::apiResource('permisos',PermisosController::class);
Route::apiResource('roles',RolesController::class);
Route::apiResource('usuarios',UsuariosController::class);

Route::apiResource('dependencias',DependenciasController::class)->except(['show','destroy']);
Route::apiResource('perfiles',PerfilesController::class)->except(['show','destroy']);
Route::apiResource('paginas',PaginasController::class);
Route::apiResource('menus',MenusController::class);

Route::put('usuarios/reiniciar-password/{usuario}',[UsuariosController::class,'restartPassword']);
Route::put('usuarios/actualizar-password/{usuario}',[UsuariosController::class,'updatePassword']);