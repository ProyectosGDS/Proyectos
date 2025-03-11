<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\eventos;
use Illuminate\Http\Request;

class EventosController extends Controller
{
    public function index() {
        try {
            $eventos = eventos::with([
                'tipo_evento',
                'estado_evento',
                'dependencia',
                'creado_por',
            ])->latest('id_evento')->get();
            return response($eventos);
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function show(eventos $evento) {
        try {
            return response($evento);
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function store(Request $request) {
        
        $firstDayYear = date('Y').'-01-01';
        
        $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'ubicacion' => 'required|string|max:500',
            'fecha_ini' => 'required|date|date_format:Y-m-d|after:'.$firstDayYear,
            'fecha_fin' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_ini',
            'hora_ini' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after_or_equal:hora_ini',
            'responsable' => 'required|string',
            'id_tipo_evento' => 'required',
        ]);

        try {

            eventos::create([
                'titulo' => mb_strtoupper($request->titulo),
                'descripcion' => mb_strtoupper($request->descripcion),
                'ubicacion' => mb_strtoupper($request->ubicacion),
                'fecha_ini' => $request->fecha_ini,
                'fecha_fin' => $request->fecha_fin,
                'hora_ini' => $request->hora_ini,
                'hora_fin' => $request->hora_fin,
                'responsable' => mb_strtoupper($request->responsable),
                'duracion' => $request->duracion ?? null,
                'usuario' => auth()->user()->cui,
                'fechau' => now(),
                'id_estatus' => 2,
                'id_tipo_evento' => $request->id_tipo_evento,
                'id_dependencia' => $request->id_dependencia ?? auth()->user()->id_dependencia,
            ]);

            return response('Evento creado exitosamente.');
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function update(Request $request, eventos $evento) {
        
        $firstDayYear = date('Y').'-01-01';
        
        $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'ubicacion' => 'required|string|max:500',
            'fecha_ini' => 'required|date|date_format:Y-m-d|after:'.$firstDayYear,
            'fecha_fin' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_ini',
            'hora_ini' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after_or_equal:hora_ini',
            'responsable' => 'required|string',
            'id_estatus' => 'required',
            'id_tipo_evento' => 'required',
        ]);

        try {

            
                $evento->titulo = mb_strtoupper($request->titulo);
                $evento->descripcion = mb_strtoupper($request->descripcion);
                $evento->ubicacion = mb_strtoupper($request->ubicacion);
                $evento->fecha_ini = $request->fecha_ini;
                $evento->fecha_fin = $request->fecha_fin;
                $evento->hora_ini = $request->hora_ini;
                $evento->hora_fin = $request->hora_fin;
                $evento->responsable = mb_strtoupper($request->responsable);
                $evento->duracion = $request->duracion ?? null;
                $evento->id_estatus = $request->id_estatus;
                $evento->id_tipo_evento = $request->id_tipo_evento;
                $evento->id_dependencia = $request->id_dependencia ?? auth()->user()->id_dependencia;
                $evento->save();

            return response('Evento modificado exitosamente.');

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function changeStatus(Request $request, eventos $evento) {
        $request->validate([
            'id_estatus' => 'required'
        ]);

        try {
            
            $evento->id_estatus = $request->id_estatus;
            $evento->save();
            return response('Estatus del evento cambiado exitosamente');

        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }
}
