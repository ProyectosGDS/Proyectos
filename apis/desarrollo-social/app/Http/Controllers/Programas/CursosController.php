<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\cursos;
use Illuminate\Http\Request;

class CursosController extends Controller
{
    public function index () {
        try {

            $cursos = cursos::latest('id')
                ->get();

            return response($cursos);  

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

            $curso = cursos::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => $request->descripcion ?? null,
                'estado' => 'A',
            ]);

            return response('curso creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (cursos $curso) {
        try {
            return response($curso->load(['detalles']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, cursos $curso) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'required|string|max:255',
        ]);

        try {

            $curso->nombre = mb_strtoupper($request->nombre);
            $curso->descripcion = $request->descripcion ?? null;
            $curso->estado = $request->estado;
            $curso->save();

            return response('curso modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (cursos $curso) {
        try {
            $curso->estado = 'I';
            $curso->save();
            
            return response('curso desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
