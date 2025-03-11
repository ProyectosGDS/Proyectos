<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\eventos;

class EventosController extends Controller
{
    public function index() {
        try {
            $eventos = eventos::with([
                'tipo_evento',
                'estado_evento',
                'dependencia',
                'creado_por',
            ])->where('id_estatus',1)
            ->latest('id_evento')->get();

            $eventos = $eventos->map(function($evento){
                return [
                    'title' => $evento->titulo,
                    'description' => $evento->descripcion,
                    'fecha' => $evento->fecha,
                    'hora' => $evento->hora,
                    'date' => [
                        'start' => $evento->fecha_ini,
                        'end' => $evento->fecha_fin
                    ],
                    'time' => [
                        'start' => $evento->hora_ini,
                        'end' => $evento->hora_fin,
                    ]
                ];
            })->values();

            return response($eventos);
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
