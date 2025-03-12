<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\detalles_actividades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DetallesActividadesController extends Controller
{
    public function index (Request $request) {

        $year = $request->input('year');
        $programa_id = $request->input('programa_id',null);

        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            $query = " SELECT
                    da.id,
                    p.nombre programa,
                    a.nombre actividad,
                    a.descripcion descripcion,
                    da.responsable,
                    z.descripcion zona,
                    d.nombre distrito,
                    da.coordenadas,
                    da.direccion,
                    CONCAT(da.fecha_inicial,' - ',da.fecha_final) fecha,
                    CONCAT(da.hora_inicio,' A ',da.hora_final) hora,
                    ta.nombre tipo,
                    ea.nombre estado
                FROM detalles_actividades da
                    INNER JOIN programas p
                        ON da.programa_id = p.id
                    INNER JOIN estados_actividades ea
                        ON da.estado_actividad_id = ea.id
                    INNER JOIN tipos_actividades ta
                        ON da.tipo_actividad_id = ta.id
                    INNER JOIN actividades a
                        ON da.actividad_id = a.id
                    INNER JOIN zonas z
                        ON da.zona_id = z.id
                    INNER JOIN distritos d
                        ON da.distrito_id = d.id
                    WHERE YEAR(da.fecha_inicial) = ?
            ";

            if ($perfil) {
                if(!is_null($programa_id)) {
                    $query .= " AND da.programa_id = ? ";
                }

                $query .= " ORDER BY da.id DESC";
                
            } else {

                $query .= " AND da.programa_id = ?  
                    ORDER BY da.id DESC
                ";
            }


            $detalles_actividades = DB::connection('gds')->select($query,[$year, $programa_id]);

            return response($detalles_actividades);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'responsable' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:100',
            'hora_inicio' => 'nullable|required_with:hora_final|date_format:H:i',
            'hora_final' => 'nullable|required_with:hora_inicio|date_format:H:i|after_or_equal:hora_inicio',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after_or_equal:fecha_inicial',
            'coordenadas' => 'nullable|string|max:100',
            'zona_id' => 'nullable|numeric',
            'distrito_id' => 'nullable|numeric',
            'actividad_id' => 'required|numeric',
            'tipo_actividad_id' => 'required|numeric',
            'programa_id' => 'required|numeric',
        ]);

        try {

            $actividad = detalles_actividades::create([
                'responsable' => $request->responsable ?? null,
                'direccion' => $request->direccion ?? null,
                'hora_inicio' => $request->hora_inicio ?? null,
                'hora_final' => $request->hora_final ?? null,
                'fecha_inicial' => $request->fecha_inicial ?? null,
                'fecha_final' => $request->fecha_final ?? null,
                'coordenadas' => $request->coordenadas ?? null,
                'zona_id' => $request->zona_id ?? null, 
                'distrito_id' => $request->distrito_id ?? null,
                'actividad_id' => $request->actividad_id,
                'tipo_actividad_id' => $request->tipo_actividad,
                'estado_actividad_id' => 2,
                'programa_id' => $request->programa_id,
            ]);

            return response('Actividad creada correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (detalles_actividades $actividad) {
        try {
            return response($actividad->load([
                'programa',
                'estado',
                'tipo',
                'actividad',
                'zona',
                'distrito'
            ]));  
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function update (Request $request, detalles_actividades $actividad) {
        $request->validate([
            'responsable' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:100',
            'hora_inicio' => 'nullable|required_with:hora_final|date_format:H:i',
            'hora_final' => 'nullable|required_with:hora_inicio|date_format:H:i|after_or_equal:hora_inicio',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after_or_equal:fecha_inicial',
            'coordenadas' => 'nullable|string|max:100',
            'zona_id' => 'nullable|numeric',
            'distrito_id' => 'nullable|numeric',
            'actividad_id' => 'required|numeric',
            'tipo_actividad_id' => 'required|numeric',
            'programa_id' => 'required|numeric',
        ]);

        try {
                $actividad->responsable = $request->responsable ? mb_strtoupper($request->responsable) : null;
                $actividad->direccion = $request->direccion ?? null;
                $actividad->hora_inicio = $request->hora_inicio ?? null;
                $actividad->hora_final = $request->hora_final ?? null;
                $actividad->fecha_inicial = $request->fecha_inicial ?? null;
                $actividad->fecha_final = $request->fecha_final ?? null;
                $actividad->coordenadas = $request->coordenadas ?? null;
                $actividad->zona_id = $request->zona_id ?? null;
                $actividad->distrito_id = $request->distrito_id ?? null;
                $actividad->actividad_id = $request->actividad_id;
                $actividad->tipo_actividad_id = $request->tipo_actividad_id;
                $actividad->estado_actividad_id = $request->estado_actividad_id;
                $actividad->programa_id = $request->programa_id;
                $actividad->save();

            return response('Actividad modificada correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function disabled (detalles_actividades $actividad) {
        try {
                $actividad->estado = 'I';
                $actividad->save();

            return response('Actividad deshabilitada correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (detalles_actividades $actividad) {
        try {

            $actividad->delete();
            
            return response('Actividad eliminada correctamente');  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
