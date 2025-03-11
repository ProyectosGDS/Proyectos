<?php

namespace App\Http\Controllers\Globales;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'index' => 'required|numeric',
            'tabla' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
        ]);

        try {
            $bitacora = bitacora::create([
                'accion' => bitacora::$acciones[$request->index],
                'tabla' => $request->tabla ?? null,
                'descripcion' => $request->descripcion,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $request->beneficiario_id ?? null,
            ]);

            return response('Se ha almacenado el registro correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show(string $indexes) {

        $indexes = explode(",",$indexes);

        $acciones = [];

        foreach ($indexes as $index) {
            array_push($acciones,bitacora::$acciones[$index]);
        }

        try {

            $bitacoras = bitacora::with(['usuario:id,nombre,dependencia_id','beneficiario:id,primer_nombre,primer_apellido'])
                ->whereIn('accion',$acciones)
                ->latest('id')
                ->get();

            return response($bitacoras);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
