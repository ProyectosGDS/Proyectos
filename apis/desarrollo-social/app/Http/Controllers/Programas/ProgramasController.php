<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\detalles_actividades;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\programas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramasController extends Controller
{
    public function index () {
        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            if ($perfil) {
                $programas = programas::with(['dependencia','modulos'])
                    ->latest('id')
                    ->get();
                return response($programas);
            }

            $programas = programas::whereHas('dependencia',function($query){
                    $query->where('dependencia_id',auth()->user()->dependencia_id);
                })
                ->with(['dependencia','modulos'])
                ->latest('id')
                ->get();
            return response($programas);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:255',
        ]);

        try {

            $dependencia_id = null;

            if($request->dependencia_id){
                $dependencia_id = $request->dependencia_id;    
            }


            $programa = programas::create([
                'nombre' => mb_strtoupper($request->nombre),
                'descripcion' => $request->descripcion ?? null,
                'dependencia_id' => $dependencia_id ?? auth()->user()->dependencia_id,
                'estado' => 'A',
            ]);

            return response('Programa creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (programas $programa) {
        try {
            return response($programa->load(['dependencia','modulos.programa']));  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, programas $programa) {
        $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:255',
            'dependencia_id' => 'required',
        ]);

        try {

            $programa->nombre = mb_strtoupper($request->nombre);
            $programa->descripcion = $request->descripcion ?? null;
            $programa->dependencia_id = $request->dependencia_id;
            $programa->estado = $request->estado;
            $programa->save();

            return response('Programa modificado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (programas $programa) {
        try {
            $programa->estado = 'I';
            $programa->save();
            
            return response('Programa desactivado correctamente');  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_cursos (int $programa_id) {
        try {

            $query = "
                SELECT
                    dc.id,
                    p.nombre programa,
                    c.nombre curso,
                    dc.seccion,
                    i.nombre instructor,
                    UPPER(CONCAT(s.nombre,' ',z.descripcion,' ',d.nombre,' ',s.direccion)) sede,
                    UPPER(CONCAT(h.hora_inicial,' a ',h.hora_final,' - ',h.lun,' ',h.mar,' ',h.mie,' ',h.jue )) horario,
                    t.nombre temporalidad,
                    dc.modalidad,
                    dc.capacidad,
                    dc.fecha_inicial,
                    dc.fecha_final,
                    dc.publico,
                    dc.estado,
                    p.dependencia_id
                FROM detalles_cursos dc
                LEFT JOIN cursos_modulos cm
                    ON dc.id = cm.detalle_curso_id
                    INNER JOIN programas p
                        ON dc.programa_id = p.id
                    INNER JOIN cursos c
                        ON dc.curso_id = c.id
                    INNER JOIN instructores i
                        ON dc.instructor_id = i.id
                    INNER JOIN sedes s
                        ON dc.sede_id = s.id
                        INNER JOIN zonas z
                            ON s.zona_id = z.id
                        INNER JOIN distritos d
                            ON s.distrito_id = d.id
                    INNER JOIN horarios h
                        ON dc.horario_id = h.id 
                    INNER JOIN temporalidades t
                        ON dc.temporalidad_id = t.id
                WHERE cm.modulo_id IS NULL
                AND dc.programa_id = ?
                ORDER BY dc.id DESC
            ";

            $cursos_programa = DB::connection('gds')->select($query,[$programa_id]);

            return response($cursos_programa);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_cursos(Request $request) {
        $request->validate([
            'cursos' => 'required|array'
        ]);
        try {

            $count_cursos = 0;

            foreach ($request->cursos as $curso) {

                if(!isset($curso['id'])) {
                    detalles_cursos::create([
                        'seccion' => $curso['seccion'] ?? null,
                        'capacidad' => $curso['capacidad'],
                        'modalidad' => $curso['modalidad'],
                        'curso_id' => $curso['curso_id'],
                        'instructor_id' => $curso['instructor_id'],
                        'sede_id' => $curso['sede_id'],
                        'horario_id' => $curso['horario_id'],
                        'programa_id' => $curso['programa_id'],
                        'temporalidad_id' => $curso['temporalidad_id'],
                        'fecha_inicial' => $curso['fecha_inicial'],
                        'fecha_final' => $curso['fecha_final'],
                        'publico' => 'S',
                        'estado' => 'A',
                    ]);

                    $count_cursos ++;
                }
            }

            return response($count_cursos.' Cursos asignados correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_beneficiarios (int $programa, int $year) {
        try {
            
            $query = "
                SELECT
                    bm.id inscripcion_id,
                    b.cui,
                    CONCAT(b.primer_nombre,' ',b.primer_apellido) AS beneficiario,
                    p.nombre programa,
                    m.nombre modulo_curso,
                    bm.created_at fecha_inscripcion,
                    bm.estado,
                    'MODULO' tipo
                FROM beneficiarios_modulos bm
                INNER JOIN beneficiarios b
                    ON bm.beneficiario_id = b.id
                INNER JOIN modulos m
                    ON bm.modulo_id = m.id
                INNER JOIN programas p
                    ON m.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(bm.created_at) = ?
                
                UNION ALL
                    
                SELECT
                    bc.id inscripcion_id,
                    b.cui,
                    CONCAT(b.primer_nombre,' ',b.primer_apellido) AS beneficiario,
                    p.nombre programa,
                    c.nombre modulo_curso,
                    bc.created_at fecha_inscripcion,
                    bc.estado,
                    'CURSO' tipo
                FROM beneficiarios_cursos bc
                INNER JOIN beneficiarios b
                    ON bc.beneficiario_id = b.id
                INNER JOIN detalles_cursos dc
                    ON bc.detalle_curso_id = dc.id
                INNER JOIN cursos c
                    ON dc.curso_id = c.id
                INNER JOIN programas p
                    ON dc.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(bc.created_at) = ?

                UNION ALL

                SELECT
                    ba.id inscripcion_id,
                    b.cui,
                    CONCAT(b.primer_nombre,' ',b.primer_apellido) AS beneficiario,
                    p.nombre programa,
                    a.nombre modulo_curso,
                    ba.created_at fecha_inscripcion,
                    ba.estado,
                    ta.nombre
                FROM beneficiarios_actividades ba
                INNER JOIN beneficiarios b
                    ON ba.beneficiario_id = b.id
                INNER JOIN detalles_actividades da
                    ON ba.detalle_actividad_id = da.id
                INNER JOIN actividades a
                    ON da.actividad_id = a.id
                INNER JOIN tipos_actividades ta
                    ON da.tipo_actividad_id = ta.id
                INNER JOIN programas p
                        ON da.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(ba.created_at) = ?

                ORDER BY beneficiario
            ";

            $queryTotal = "
                SELECT DISTINCT
                    b.cui
                FROM beneficiarios_modulos bm
                INNER JOIN beneficiarios b
                        ON bm.beneficiario_id = b.id
                INNER JOIN modulos m
                        ON bm.modulo_id = m.id
                INNER JOIN programas p
                        ON m.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(bm.created_at) = ?

                UNION
                        
                SELECT DISTINCT
                    b.cui
                FROM beneficiarios_cursos bc
                INNER JOIN beneficiarios b
                        ON bc.beneficiario_id = b.id
                INNER JOIN detalles_cursos dc
                        ON bc.detalle_curso_id = dc.id
                INNER JOIN programas p
                        ON dc.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(bc.created_at) = ?

                UNION

                SELECT DISTINCT
                        b.cui
                FROM beneficiarios_actividades ba
                INNER JOIN beneficiarios b
                                ON ba.beneficiario_id = b.id
                INNER JOIN detalles_actividades da
                                ON ba.detalle_actividad_id = da.id
                INNER JOIN programas p
                                ON da.programa_id = p.id
                WHERE p.id = ?
                AND YEAR(ba.created_at) = ?
            ";

            $beneficiarios_inscritos = DB::connection('gds')->select($query,[$programa,$year,$programa,$year,$programa,$year]);

            $total_beneficiario_unico = DB::connection('gds')->select($queryTotal,[$programa,$year,$programa,$year,$programa,$year]);

            return response([
                'total_beneficiario_unico' => collect($total_beneficiario_unico)->count(),
                'beneficiarios_inscritos' => $beneficiarios_inscritos
            ]);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_actividades (int $programa_id, int $year) {
        try {

            $query = "
                SELECT
                    da.*,
                    p.nombre programa,
                    a.nombre actividad,
                    da.responsable,
                    z.descripcion zona,
                    d.nombre distrito,
                    da.direccion,
                    da.coordenadas,
                    CONCAT(da.hora_inicio,' A ',da.hora_final) horario,
                    CONCAT(da.fecha_inicial,' - ',da.fecha_final) fechas,
                    ta.nombre tipo,
                    ea.nombre estado,
                    da.estado_actividad_id
                FROM detalles_actividades da
                    INNER JOIN programas p
                        ON da.programa_id = p.id
                    LEFT JOIN zonas z
                        ON da.zona_id = z.id
                    LEFT JOIN distritos d
                        ON da.distrito_id = d.id
                    INNER JOIN actividades a
                        ON da.actividad_id = a.id
                    LEFT JOIN tipos_actividades ta
                        ON da.tipo_actividad_id = ta.id
                    LEFT JOIN estados_actividades ea
                        ON da.estado_actividad_id = ea.id
                    WHERE YEAR(da.fecha_inicial) = ?
                    AND da.programa_id = ?
                    ORDER BY da.id DESC        
            ";

            $actividades_programa = DB::connection('gds')->select($query,[$year,$programa_id]);
            
            return response($actividades_programa);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_actividades(Request $request) {
        $request->validate([
            'actividades' => 'required|array'
        ]);
        try {

            $count_actividades = 0;

            foreach ($request->actividades as $actividad) {

                if(!isset($actividad['id'])) {

                    detalles_actividades::create([
                            'responsable' => $actividad['responsable'] ?? null,
                            'direccion' => $actividad['direccion'] ?? null,
                            'hora_inicio' => $actividad['hora_inicio'] ?? null,
                            'hora_final' => $actividad['hora_final'] ?? null,
                            'fecha_inicial' => $actividad['fecha_inicial'],
                            'fecha_final' => $actividad['fecha_final'],
                            'coordenadas' => $actividad['coordenadas'] ?? null,
                            'zona_id' => $actividad['zona_id'] ?? null, 
                            'distrito_id' => $actividad['distrito_id'] ?? null,
                            'actividad_id' => $actividad['actividad_id'],
                            'tipo_actividad_id' => $actividad['tipo_actividad_id'],                            
                            'programa_id' => $actividad['programa_id'],
                            'estado_actividad_id' => 2
                    ]);

                    $count_actividades ++;
                }
            }

            return response($count_actividades.' Actividades asignadas correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

}
