<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\estatus_eventos;
use Illuminate\Http\Request;

class EstatusEventoController extends Controller
{
    public function index() {
        try {
            $estatus_eventos = estatus_eventos::all();
            return response($estatus_eventos);  
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
