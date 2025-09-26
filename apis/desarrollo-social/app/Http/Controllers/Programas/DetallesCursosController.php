<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\tarifas_cursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallesCursosController extends Controller
{
    public function index () {
        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            $query = "
                SELECT
                    dc.id,
                    p.nombre programa,
                    m.nombre modulo,
                    c.nombre curso,
                    dc.seccion,
                    i.nombre instructor,
                    upper(concat(s.nombre,' ',s.direccion,' ',z.descripcion,' ',d.nombre)) sede,
                    upper(concat(h.hora_inicial,' a ',h.hora_final,' - ',h.lun,' ',h.mar,' ',h.mie,' ',h.jue )) horario,
                    t.nombre temporalidad,
                    dc.modalidad,
                    dc.capacidad,
                    dc.fecha_inicial,
                    dc.fecha_final,
                    dc.publico,
                    dc.estado,
                    p.dependencia_id
                FROM ADM_GDS.programas p
                INNER JOIN ADM_GDS.modulos m
                    ON m.programa_id = p.id
                    INNER JOIN ADM_GDS.cursos_modulos cm
                        ON cm.modulo_id = m.id 
                    INNER JOIN ADM_GDS.detalles_cursos dc
                            ON cm.detalle_curso_id = dc.id
                    INNER JOIN ADM_GDS.cursos c
                            ON dc.curso_id = c.id
                    INNER JOIN ADM_GDS.instructores i
                            ON dc.instructor_id = i.id
                    INNER JOIN ADM_GDS.sedes s
                            ON dc.sede_id = s.id
                            INNER JOIN ADM_GDS.zonas z
                                    ON s.zona_id = z.id
                            INNER JOIN ADM_GDS.distritos d
                                    ON s.distrito_id = d.id
                    INNER JOIN ADM_GDS.horarios h
                            ON dc.horario_id = h.id 
                    INNER JOIN ADM_GDS.temporalidades t
                            ON dc.temporalidad_id = t.id
            ";

            if ($perfil) {
                $query .= " order by dc.id desc";
                $detalles_cursos = DB::connection('gds')->select($query);
                return response($detalles_cursos);
            }

            $query .= " 
                 where p.dependencia_id = ?  
                 order by dc.id desc
            ";
            $detalles_cursos = DB::connection('gds')->select($query,[auth()->user()->dependencia_id]);

            return response($detalles_cursos);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'seccion' => 'nullable|string|max:45',
            'capacidad' => 'required|numeric',
            'modalidad' => 'required|string|max:25',
            'curso_id' => 'required',
            'instructor_id' => 'required',
            'sede_id' => 'required',
            'horarios' => 'required|array',
            'programa_id' => 'required',
            'temporalidad_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial',
            'paga' => 'required|in:S,N',
            'tarifas.tarifa_menor' => 'required_if:paga,S|decimal:2',
            'tarifas.tarifa_mayor' => 'required_if:paga,S|decimal:2',
            'tarifas.temporalidad' => 'required_if:paga,S',
        ]);

        try {

            $curso = detalles_cursos::create([
                'seccion' => $request->seccion ?? null,
                'capacidad' => $request->capacidad,
                'modalidad' => $request->modalidad,
                'curso_id' => $request->curso_id,
                'instructor_id' => $request->instructor_id,
                'sede_id' => $request->sede_id,
                'programa_id' => $request->programa_id,
                'temporalidad_id' => $request->temporalidad_id,
                'fecha_inicial' => $request->fecha_inicial ?? null,
                'fecha_final' => $request->fecha_final ?? null,
                'publico' => 'S',
                'estado' => 'A',
                'paga' => $request->paga ?? 'N',
            ]);

            if(isset($curso->id)) {
                $curso->horarios()->sync($request->horarios);
                if($request->paga == 'S') {
                    tarifas_cursos::create([
                        'tipo' => 'CURSO',
                        'curso_modulo_id' => $curso->id,
                        'inscripcion' => $request->tarifas['inscripcion'] ?? null,
                        'tarifa_menor' => $request->tarifas['tarifa_menor'],
                        'tarifa_mayor' => $request->tarifas['tarifa_mayor'],
                        'temporalidad' => $request->tarifas['temporalidad'],
                    ]);
                }
            }

            return response('curso creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (detalles_cursos $curso) {
        try {
            return response($curso->load([
                'programa',
                'modulo',
                'curso',
                'instructor',
                'sede.zona',
                'sede.distrito',
                'horarios',
                'temporalidad',
                'tarifas'
            ]));  
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function update (Request $request, detalles_cursos $curso) {
        $request->validate([
            'capacidad' => 'nullable|numeric',
            'seccion' => 'nullable|string|max:45',
            'sede_id' => 'required',
            'modalidad' => 'present:capacidad',
            'temporalidad_id' => 'required',
            'curso_id' => 'required',
            'instructor_id' => 'required',
            'horarios' => 'required|array',
            'programa_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial',
            'paga' => 'nullable|in:S,N',
            'tarifas.tarifa_menor' => 'required_if:paga,S|decimal:2',
            'tarifas.tarifa_mayor' => 'required_if:paga,S|decimal:2',
            'tarifas.temporalidad' => 'required_if:paga,S',
            'tarifas.no_cuotas' => 'required_if:paga,S|integer|min:1',
            'tarifas.mes_inicial' => 'required_if:paga,S|date|date_format:Y-m',
            'tarifas.mes_final' => 'required_if:paga,S|date|date_format:Y-m|after_or_equal:mes_inicial',
        ]);

        try {
                $curso->seccion = $request->seccion ?? null;
                $curso->capacidad = $request->capacidad;
                $curso->modalidad = $request->modalidad;
                $curso->curso_id = $request->curso_id;
                $curso->instructor_id = $request->instructor_id;
                $curso->sede_id = $request->sede_id;
                $curso->programa_id = $request->programa_id;
                $curso->temporalidad_id = $request->temporalidad_id;
                $curso->fecha_inicial = $request->fecha_inicial ?? null;
                $curso->fecha_final = $request->fecha_final ?? null;
                $curso->publico = $request->publico ?? null;
                $curso->estado = $request->estado;
                $curso->paga = $request->paga;
                $curso->save();
                
                if($request->paga == 'S') {
                    $curso->tarifas->inscripcion = $request->tarifas['inscripcion'] ?? null;
                    $curso->tarifas->tarifa_menor = $request->tarifas['tarifa_menor'];
                    $curso->tarifas->tarifa_mayor = $request->tarifas['tarifa_mayor'];
                    $curso->tarifas->temporalidad = $request->tarifas['temporalidad'];
                    $curso->tarifas->no_cuotas = $request->tarifas['no_cuotas'];
                    $curso->tarifas->mes_inicial = $request->tarifas['mes_inicial'];
                    $curso->tarifas->mes_final = $request->tarifas['mes_final'];
                    $curso->tarifas->save();
                } else {
                    $curso->tarifas()->delete();
                }

            return response('curso modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function disabled (detalles_cursos $curso) {
        try {
                $curso->estado = 'I';
                $curso->save();

            return response('Curso deshabilitado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (detalles_cursos $curso) {
        try {

            $curso->delete();
            
            return response('Curso eliminado correctamente');  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function getRequirements(detalles_cursos $curso) {
        try {
            return response($curso->load('requisitos'));
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function assigRequirements(Request $request, detalles_cursos $curso) {
        try {
            $curso->requisitos()->sync($request->requisitos);
            return response('Requisitos asignados exitosamente.');      
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function syncHorarios(Request $request, detalles_cursos $curso) {
        try {
            $curso->horarios()->sync(collect($request->horarios)->pluck('id'));
            return response('Horarios asignados exitosamente.');      
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
    
}
