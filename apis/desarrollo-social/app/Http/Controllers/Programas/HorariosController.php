<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\horarios;
use Illuminate\Http\Request;


class HorariosController extends Controller
{
    public function index () {
        try {

            $horarios = horarios::latest('id')
                ->get();

            return response($horarios);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'hora_inicial' => 'nullable|date_format:H:i',
            'hora_final' => 'nullable|date_format:H:i|after:hora_inicial',
            'dias' => 'nullable|array',
        ]);

        try {

            $dias = [];

            if($request->dias) {
                foreach ($request->dias as $dia) {
                    $dias[$dia] = $dia;
                }
            }

            $horario = horarios::create([
                'hora_inicial' => $request->hora_inicial ?? null,
                'hora_final' => $request->hora_final ?? null,
                'lun' => $dias['lun'] ?? null,
                'mar' => $dias['mar'] ?? null,
                'mie' => $dias['mie'] ?? null,
                'jue' => $dias['jue'] ?? null,
                'vie' => $dias['vie'] ?? null,
                'sab' => $dias['sab'] ?? null,
                'dom' => $dias['dom'] ?? null,
                'estado' => 'A',
            ]);

            return response('horario creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (horarios $horario) {
        try {
            return response($horario->load(['cursos']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, horarios $horario) {
        $request->validate([
            'hora_inicial' => 'nullable|date_format:H:i',
            'hora_final' => 'nullable|date_format:H:i|after:hora_inicial',
            'dias' => 'nullable|array',
        ]);

        try {

            $dias = [];

            if($request->dias) {
                foreach ($request->dias as $dia) {
                    $dias[$dia] = $dia;
                }
            }

            $horario->hora_inicial = $request->hora_inicial ?? null;
            $horario->hora_final = $request->hora_final ?? null;
            $horario->lun = $dias['lun'] ?? null;
            $horario->mar = $dias['mar'] ?? null;
            $horario->mie = $dias['mie'] ?? null;
            $horario->jue = $dias['jue'] ?? null;
            $horario->vie = $dias['vie'] ?? null;
            $horario->sab = $dias['sab'] ?? null;
            $horario->dom = $dias['dom'] ?? null;
            $horario->estado = $request->estado;                
            $horario->save();

            return response('horario modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (horarios $horario) {
        try {
            $horario->estado = 'I';
            $horario->save();
            
            return response('horario desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
