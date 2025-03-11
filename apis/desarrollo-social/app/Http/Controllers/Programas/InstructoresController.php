<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\instructores;
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstructoresController extends Controller
{
    public function index () {
        try {

            $instructores = instructores::latest('id')
                ->get();

            return response($instructores);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        try {

            $instructor = instructores::create([
                'nombre' => ucwords($request->nombre),
                'estado' => 'A',
            ]);

            return response('curso creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (instructores $instructor) {
        try {
            return response($instructor->load(['cursos']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, instructores $instructor) {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        try {

            $instructor->nombre = mb_strtoupper($request->nombre);
            $instructor->estado = $request->estado;
            $instructor->save();

            return response('curso modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (instructores $instructor) {
        try {
            $instructor->estado = 'I';
            $instructor->save();
            
            return response('curso desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
