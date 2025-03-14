<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\eventos;

class EventosController extends Controller
{
    public function index() {
        try {
            $eventos = eventos::with([
                'tipo',
                'estado',
                'dependencia',
                'usuario',
            ])
            ->where('estado_evento_id',1)
            ->whereYear('fecha_inicial',date('Y'))
            ->latest('id')
            ->get();

            $eventos = $eventos->map(function($evento){
                return [
                    'title' => $evento->titulo,
                    'description' => $evento->descripcion,
                    'fecha' => $evento->fecha,
                    'hora' => $evento->hora,
                    'date' => [
                        'start' => $evento->fecha_inicial,
                        'end' => $evento->fecha_final
                    ],
                    'time' => [
                        'start' => $evento->hora_inicial,
                        'end' => $evento->hora_final,
                    ]
                ];
            })->values();

            return response($eventos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
