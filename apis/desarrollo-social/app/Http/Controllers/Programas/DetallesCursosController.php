<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\detalles_cursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallesCursosController extends Controller
{
    public function index () {
        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            $query = "
                select
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
                from programas p
                inner join modulos m
                    on m.programa_id = p.id
                    inner join cursos_modulos cm
                        on cm.modulo_id = m.id 
                    inner join detalles_cursos dc
                            on cm.detalle_curso_id = dc.id
                    inner join cursos c
                            on dc.curso_id = c.id
                    inner join instructores i
                            on dc.instructor_id = i.id
                    inner join sedes s
                            on dc.sede_id = s.id
                            inner join zonas z
                                    on s.zona_id = z.id
                            inner join distritos d
                                    on s.distrito_id = d.id
                    inner join horarios h
                            on dc.horario_id = h.id 
                    inner join temporalidades t
                            on dc.temporalidad_id = t.id
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
            'horario_id' => 'required',
            'programa_id' => 'required',
            'temporalidad_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial'
        ]);

        try {

            $curso = detalles_cursos::create([
                'seccion' => $request->seccion ?? null,
                'capacidad' => $request->capacidad,
                'modalidad' => $request->modalidad,
                'curso_id' => $request->curso_id,
                'instructor_id' => $request->instructor_id,
                'sede_id' => $request->sede_id,
                'horario_id' => $request->horario_id,
                'programa_id' => $request->programa_id,
                'temporalidad_id' => $request->temporalidad_id,
                'fecha_inicial' => $request->fecha_inicial ?? null,
                'fecha_final' => $request->fecha_final ?? null,
                'publico' => 'S',
                'estado' => 'A'
            ]);

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
                'horario',
                'temporalidad',
            ]));  
        } catch (\Throwable $th) {
            return response($th->getMessage(),422);
        }
    }

    public function update (Request $request, detalles_cursos $curso) {
        $request->validate([
            'seccion' => 'nullable|string|max:45',
            'capacidad' => 'nullable|numeric',
            'modalidad' => 'required|string|max:25',
            'curso_id' => 'required',
            'instructor_id' => 'required',
            'sede_id' => 'required',
            'horario_id' => 'required',
            'programa_id' => 'required',
            'temporalidad_id' => 'required',
            'fecha_inicial' => 'nullable|required_with:fecha_final|date|date_format:Y-m-d',
            'fecha_final' => 'nullable|required_with:fecha_inicial|date|date_format:Y-m-d|after:fecha_inicial'
        ]);

        try {
                $curso->seccion = $request->seccion ?? null;
                $curso->capacidad = $request->capacidad;
                $curso->modalidad = $request->modalidad;
                $curso->curso_id = $request->curso_id;
                $curso->instructor_id = $request->instructor_id;
                $curso->sede_id = $request->sede_id;
                $curso->horario_id = $request->horario_id;
                $curso->programa_id = $request->programa_id;
                $curso->temporalidad_id = $request->temporalidad_id;
                $curso->fecha_inicial = $request->fecha_inicial ?? null;
                $curso->fecha_final = $request->fecha_final ?? null;
                $curso->publico = $request->publico ?? null;
                $curso->estado = $request->estado;
                $curso->save();

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
    
}
