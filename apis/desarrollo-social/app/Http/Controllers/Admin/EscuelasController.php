<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\escuelas;
use Illuminate\Http\Request;

class EscuelasController extends Controller
{
    public function index (Request $request) {
        try {
            
            $escuelas = escuelas::with(['programas','dependencia'])
                ->where('dependencia_id',$request->dependencia_id)
                ->get();

            return response($escuelas);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {

        $request->validate([
            'nombre' => 'required|string|max:255',
            'objeto_contrato' => 'nullable|string|max:90',
            'dependencia_id' => 'required|integer|exists:dependencias,id',
        ]);

        try {
            
            escuelas::create([
                'nombre' => mb_strtoupper(trim($request->nombre)),
                'objeto_contrato' => $request->objeto_contrato ?? null,
                'dependencia_id' => $request->dependencia_id
            ]);

            return response('Escuela creada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (escuelas $escuela) {

        try {

            return response($escuela->load(['dependencia','programas']));

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, escuelas $escuela) {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'objeto_contrato' => 'nullable|string|max:90',
            'dependencia_id' => 'required|integer|exists:dependencias,id',
        ]);

        try {
            
            $escuela->nombre =  mb_strtoupper(trim($request->nombre));
            $escuela->objeto_contrato =  $request->objeto_contrato ?? null;
            $escuela->dependencia_id =  $request->dependencia_id;
            $escuela->save();

            return response('Escuela modificada exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

}
