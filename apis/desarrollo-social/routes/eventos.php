<?php

use App\Http\Controllers\Eventos\EventosController;
use App\Models\adm_gds\dependencias;
use App\Models\adm_gds\estados_eventos;
use App\Models\adm_gds\tipos_eventos;
use Illuminate\Support\Facades\Route;

Route::apiResource('eventos',EventosController::class);

Route::get('catalogos-evento',function(){
    try {
        
        $catalogos = [
            'tipos_eventos' => tipos_eventos::all(),
            'estados_eventos' => estados_eventos::all(),
        ];

        return response($catalogos);

    } catch (\Throwable $th) {
        return response($th->getMessage());
    }
});