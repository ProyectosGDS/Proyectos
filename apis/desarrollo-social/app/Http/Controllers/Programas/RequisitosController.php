<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\requisitos;
use Illuminate\Http\Request;

class RequisitosController extends Controller
{
    public function index () {
        try {

            $requisitos = requisitos::latest('id')->get();

            return response($requisitos);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
        ]);

        try {

            $requisito = requisitos::create([
                'nombre' => mb_strtoupper($request->nombre),
                'estado' => 'A',
            ]);

            return response('requisito creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (requisitos $requisito) {
        try {
            return response($requisito);  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, requisitos $requisito) {
        $request->validate([
            'nombre' => 'required|string|max:80',
        ]);

        try {

            $requisito->nombre = mb_strtoupper($request->nombre);
            $requisito->estado = $request->estado;
            $requisito->save();

            return response('requisito modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (requisitos $requisito) {
        try {
            $requisito->estado = 'I';
            $requisito->save();
            
            return response('requisito desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
