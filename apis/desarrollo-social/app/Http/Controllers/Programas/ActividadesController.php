<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\actividades;
use Illuminate\Http\Request;

class ActividadesController extends Controller
{
    public function index () {
        try {

            $actividades = actividades::latest('id')->get();

            return response($actividades);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
        ]);

        try {

            $actividad = actividades::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => $request->descripcion ?? null,
                'estado' => 'A',
            ]);

            return response('Actividad creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (actividades $actividad) {
        try {
            return response($actividad->load(['detalles']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request,actividades $actividad) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'required|string|max:255'
        ]);

        try {

            $actividad->nombre = mb_strtoupper($request->nombre);
            $actividad->descripcion = $request->descripcion ?? null;
            $actividad->estado = $request->estado;
            $actividad->save();

            return response('Actividad modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (actividades $actividad) {
        try {
            $actividad->estado = 'I';
            $actividad->save();
            
            return response('Actividad eliminada correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
