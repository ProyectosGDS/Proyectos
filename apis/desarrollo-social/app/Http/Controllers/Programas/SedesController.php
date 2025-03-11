<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\sedes;
use Illuminate\Http\Request;

class SedesController extends Controller
{
    public function index () {
        try {

            $sedes = sedes::with(['zona','distrito'])
                ->latest('id')
                ->get();

            return response($sedes);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:100',
            'zona_id' => 'required',
        ]);

        try {

            $sede = sedes::create([
                'nombre' => mb_strtoupper($request->nombre),
                'direccion' => $request->direccion,
                'zona_id' => $request->zona_id,
                'distrito_id' => $request->distrito_id ?? null,
                'estado' => 'A',
            ]);

            return response('curso creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (sedes $sede) {
        try {
            return response($sede->load(['detalles']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, sedes $sede) {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:100',
            'zona_id' => 'required',
        ]);

        try {

            $sede->nombre = mb_strtoupper($request->nombre);
            $sede->direccion = $request->direccion;
            $sede->zona_id = $request->zona_id;
            $sede->distrito_id = $request->distrito_id ?? null;
            $sede->estado = $request->estado;
            $sede->save();

            return response('curso modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (sedes $sede) {
        try {
            $sede->estado = 'I';
            $sede->save();
            
            return response('curso desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
