<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\tipo_eventos;
use Illuminate\Http\Request;

class TipoEventoController extends Controller
{
    public function index() {
        try {
            $tipos_eventos = tipo_eventos::all();
            return response($tipos_eventos);  
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
