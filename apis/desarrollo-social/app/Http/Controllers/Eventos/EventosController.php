<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\eventos;
use Illuminate\Http\Request;

class EventosController extends Controller
{
    public function index(Request $request) {

        $year = $request->input('year',date('Y'));

        try {

            $eventos = eventos::with([
                'tipo',
                'estado',
                'dependencia',
                'usuario',
            ])->whereYear('fecha_inicial',$year)
            ->latest('id')
            ->get();

            return response($eventos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show(eventos $evento) {
        try {
            return response($evento);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store(Request $request) {
        
        $firstDayYear = date('Y').'-01-01';
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'ubicacion' => 'required|string|max:500',
            'fecha_inicial' => 'required|date|date_format:Y-m-d|after:'.$firstDayYear,
            'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicial',
            'hora_inicial' => 'required|date_format:H:i',
            'hora_final' => 'required|date_format:H:i|after_or_equal:hora_inicial',
            'responsable' => 'required|string|max:100',
            'tipo_evento_id' => 'required',
        ]);

        try {

            eventos::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => mb_strtoupper($request->descripcion),
                'ubicacion' => mb_strtoupper($request->ubicacion),
                'fecha_inicial' => $request->fecha_inicial,
                'fecha_final' => $request->fecha_final,
                'hora_inicial' => $request->hora_inicial,
                'hora_final' => $request->hora_final,
                'responsable' => mb_strtoupper($request->responsable),
                'duracion' => $request->duracion ?? null,
                'estado_evento_id' => 2,
                'tipo_evento_id' => $request->tipo_evento_id,
                'dependencia_id' => $request->dependencia_id ?? auth()->user()->dependencia_id,
                'usuario_id' => auth()->user()->id
            ]);

            return response('Evento creado exitosamente.');
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update(Request $request, eventos $evento) {
        
        $firstDayYear = date('Y').'-01-01';
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'ubicacion' => 'required|string|max:500',
            'fecha_inicial' => 'required|date|date_format:Y-m-d|after:'.$firstDayYear,
            'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicial',
            'hora_inicial' => 'required|date_format:H:i',
            'hora_final' => 'required|date_format:H:i|after_or_equal:hora_inicial',
            'responsable' => 'required|string|max:100',
            'estado_evento_id' => 'required',
            'tipo_evento_id' => 'required',
        ]);

        try {

            $evento->nombre = mb_strtoupper($request->nombre);
            $evento->descripcion = mb_strtoupper($request->descripcion);
            $evento->ubicacion = mb_strtoupper($request->ubicacion);
            $evento->fecha_inicial = $request->fecha_inicial;
            $evento->fecha_final = $request->fecha_final;
            $evento->hora_inicial = $request->hora_inicial;
            $evento->hora_final = $request->hora_final;
            $evento->responsable = mb_strtoupper($request->responsable);
            $evento->duracion = $request->duracion ?? null;
            $evento->estado_evento_id = $request->estado_evento_id;
            $evento->tipo_evento_id = $request->tipo_evento_id;
            $evento->dependencia_id = $request->dependencia_id ?? auth()->user()->dependencia_id;
            $evento->save();

            return response('Evento modificado exitosamente.');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy(eventos $evento) {
        try {
            $evento->delete();
            return response('Evento eliminado exitosamente');   
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
