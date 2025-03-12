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
                    DC.*,
                    DC.ID,
                    P.NOMBRE PROGRAMA,
                    C.NOMBRE CURSO,
                    DC.SECCION,
                    I.NOMBRE INSTRUCTOR,
                    UPPER(S.NOMBRE||' '||Z.DESCRIPCION||' '||D.NOMBRE||' '||S.DIRECCION) SEDE,
                    UPPER(H.HORA_INICIAL||' A '||H.HORA_FINAL||' - '||CONCATENARDIAS(H.LUN,H.MAR,H.MIE,H.JUE,H.VIE,H.SAB,H.DOM)) HORARIO,
                    T.NOMBRE TEMPORALIDAD,
                    DC.MODALIDAD,
                    DC.CAPACIDAD,
                    DC.FECHA_INICIAL,
                    DC.FECHA_FINAL,
                    DC.PUBLICO,
                    DC.ESTADO,
                    P.DEPENDENCIA_ID
                FROM DETALLES_CURSOS DC
                LEFT JOIN CURSOS_MODULOS CM
                    ON DC.ID = CM.DETALLE_CURSO_ID
                    INNER JOIN PROGRAMAS P
                        ON DC.PROGRAMA_ID = P.ID
                    INNER JOIN CURSOS C
                        ON DC.CURSO_ID = C.ID
                    INNER JOIN INSTRUCTORES I
                        ON DC.INSTRUCTOR_ID = I.ID
                    INNER JOIN SEDES S
                        ON DC.SEDE_ID = S.ID
                            INNER JOIN ZONAS Z
                                ON S.ZONA_ID = Z.ID
                            LEFT JOIN DISTRITOS D
                                ON S.DISTRITO_ID = D.ID
                    INNER JOIN HORARIOS H
                        ON DC.HORARIO_ID = H.ID 
                    INNER JOIN TEMPORALIDADES T
                        ON DC.TEMPORALIDAD_ID = T.ID
                WHERE CM.MODULO_ID IS NULL
                AND DC.PROGRAMA_ID = ?
                ORDER BY DC.ID DESC
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
                    BM.ID INSCRIPCION_ID,
                    B.CUI,
                    CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO,
                    P.NOMBRE PROGRAMA,
                    M.NOMBRE MODULO_CURSO,
                    BM.CREATED_AT FECHA_INSCRIPCION,
                    BM.ESTADO,
                    CAST('MODULO' AS VARCHAR2(50)) TIPO
                FROM BENEFICIARIOS_MODULOS BM
                INNER JOIN BENEFICIARIOS B
                    ON BM.BENEFICIARIO_ID = B.ID
                INNER JOIN MODULOS M
                    ON BM.MODULO_ID = M.ID
                INNER JOIN PROGRAMAS P
                    ON M.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BM.CREATED_AT) = ?

                UNION ALL
                        
                SELECT
                    BC.ID INSCRIPCION_ID,
                    B.CUI,
                    CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO,
                    P.NOMBRE PROGRAMA,
                    C.NOMBRE MODULO_CURSO,
                    BC.CREATED_AT FECHA_INSCRIPCION,
                    BC.ESTADO,
                    CAST('CURSO' AS VARCHAR2(50)) TIPO
                FROM BENEFICIARIOS_CURSOS BC
                INNER JOIN BENEFICIARIOS B
                    ON BC.BENEFICIARIO_ID = B.ID
                INNER JOIN DETALLES_CURSOS DC
                    ON BC.DETALLE_CURSO_ID = DC.ID
                INNER JOIN CURSOS C
                    ON DC.CURSO_ID = C.ID
                INNER JOIN PROGRAMAS P
                    ON DC.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BC.CREATED_AT) = ?

                UNION ALL

                SELECT
                    BA.ID INSCRIPCION_ID,
                    B.CUI,
                    CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO,
                    P.NOMBRE PROGRAMA,
                    A.NOMBRE MODULO_CURSO,
                    BA.CREATED_AT FECHA_INSCRIPCION,
                    BA.ESTADO,
                    CAST(TA.NOMBRE AS VARCHAR2(50)) TIPO
                FROM BENEFICIARIOS_ACTIVIDADES BA
                INNER JOIN BENEFICIARIOS B
                    ON BA.BENEFICIARIO_ID = B.ID
                INNER JOIN DETALLES_ACTIVIDADES DA
                    ON BA.DETALLE_ACTIVIDAD_ID = DA.ID
                INNER JOIN ACTIVIDADES A
                    ON DA.ACTIVIDAD_ID = A.ID
                INNER JOIN TIPOS_ACTIVIDADES TA
                    ON DA.TIPO_ACTIVIDAD_ID = TA.ID
                INNER JOIN PROGRAMAS P
                    ON DA.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BA.CREATED_AT) = ?

                ORDER BY BENEFICIARIO
            ";

            $queryTotal = "
                SELECT DISTINCT
                    B.CUI
                FROM BENEFICIARIOS_MODULOS BM
                INNER JOIN BENEFICIARIOS B
                    ON BM.BENEFICIARIO_ID = B.ID
                INNER JOIN MODULOS M
                    ON BM.MODULO_ID = M.ID
                INNER JOIN PROGRAMAS P
                    ON M.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BM.CREATED_AT) = ?

                UNION
                                
                SELECT DISTINCT
                    B.CUI
                FROM BENEFICIARIOS_CURSOS BC
                INNER JOIN BENEFICIARIOS B
                    ON BC.BENEFICIARIO_ID = B.ID
                INNER JOIN DETALLES_CURSOS DC
                    ON BC.DETALLE_CURSO_ID = DC.ID
                INNER JOIN PROGRAMAS P
                    ON DC.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BC.CREATED_AT) = ?

                UNION

                SELECT DISTINCT
                    B.CUI
                FROM BENEFICIARIOS_ACTIVIDADES BA
                INNER JOIN BENEFICIARIOS B
                    ON BA.BENEFICIARIO_ID = B.ID
                INNER JOIN DETALLES_ACTIVIDADES DA
                    ON BA.DETALLE_ACTIVIDAD_ID = DA.ID
                INNER JOIN PROGRAMAS P
                    ON DA.PROGRAMA_ID = P.ID
                WHERE P.ID = ?
                AND EXTRACT(YEAR FROM BA.CREATED_AT) = ?
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
                    DA.*,
                    P.NOMBRE PROGRAMA,
                    A.NOMBRE ACTIVIDAD,
                    Z.DESCRIPCION ZONA,
                    D.NOMBRE DISTRITO,
                    DA.HORA_INICIO ||' A '|| DA.HORA_FINAL HORARIO,
                    TO_CHAR(DA.FECHA_INICIAL,'YYYY-MM-DD') ||' - '|| TO_CHAR(DA.FECHA_FINAL,'YYYY-MM-DD') FECHAS,
                    TA.NOMBRE TIPO,
                    EA.NOMBRE ESTADO
                FROM DETALLES_ACTIVIDADES DA
                    INNER JOIN PROGRAMAS P
                            ON DA.PROGRAMA_ID = P.ID
                    LEFT JOIN ZONAS Z
                            ON DA.ZONA_ID = Z.ID
                    LEFT JOIN DISTRITOS D
                            ON DA.DISTRITO_ID = D.ID
                    INNER JOIN ACTIVIDADES A
                            ON DA.ACTIVIDAD_ID = A.ID
                    LEFT JOIN TIPOS_ACTIVIDADES TA
                            ON DA.TIPO_ACTIVIDAD_ID = TA.ID
                    LEFT JOIN ESTADOS_ACTIVIDADES EA
                            ON DA.ESTADO_ACTIVIDAD_ID = EA.ID
                WHERE EXTRACT(YEAR FROM DA.FECHA_INICIAL) = ?
                AND DA.PROGRAMA_ID = ?
                ORDER BY DA.ID DESC         
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
