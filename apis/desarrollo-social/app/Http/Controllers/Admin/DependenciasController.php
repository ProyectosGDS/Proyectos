<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\dependencias;
use Illuminate\Http\Request;

class DependenciasController extends Controller
{
    public function index () {
        try {
            
            $dependencias = dependencias::with(['programas'])->get();

            return response($dependencias);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {

        $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        try {
            
            dependencias::create([
                'nombre' => mb_strtoupper($request->nombre),
                'usuario' => 'ADM_GDS',
                'fechau' => now(),
            ]);

            return response('Dependencia creada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (dependencias $dependencia) {

        try {

            return response($dependencia);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, dependencias $dependencia) {
         $request->validate([
            'nombre' => 'required|string|max:150',
        ]);

        try {
            
            $dependencia->nombre = mb_strtoupper($request->nombre);
            $dependencia->save();

            return response('Dependencia modificada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

}
