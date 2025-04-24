<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\detalles_actividades;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use App\Models\adm_gds\programas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            return response($programa->load(['dependencia','modulos.programa','modulos.requisitos']));  
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
                    P.NOMBRE PROGRAMA,
                    C.NOMBRE CURSO,
                    I.NOMBRE INSTRUCTOR,
                    UPPER(S.NOMBRE||' '||Z.DESCRIPCION||' '||D.NOMBRE||' '||S.DIRECCION) SEDE,
                    UPPER(H.HORA_INICIAL||' A '||H.HORA_FINAL||' - '||CONCATENARDIAS(H.LUN,H.MAR,H.MIE,H.JUE,H.VIE,H.SAB,H.DOM)) HORARIO,
                    T.NOMBRE TEMPORALIDAD,
                    P.DEPENDENCIA_ID,
                    C.IMPULSATEC
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

    public function get_beneficiarios (string $programa_id, int $year) {

        $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

        $dependencia_id = auth()->user()->dependencia_id;


        try {

            $beneficiarios_cursos = DB::connection('gds')
                ->table('BENEFICIARIOS_CURSOS BC')
                ->join('BENEFICIARIOS B','BC.BENEFICIARIO_ID','=','B.ID')
                ->join('DETALLES_CURSOS DC','BC.DETALLE_CURSO_ID','=','DC.ID')
                ->join('CURSOS C','DC.CURSO_ID','=','C.ID')
                ->join('PROGRAMAS P','DC.PROGRAMA_ID','=','P.ID')
                ->join('DEPENDENCIAS D','P.DEPENDENCIA_ID','=','D.ID')
                ->select(
                    'BC.ID AS INSCRIPCION_ID',
                    'B.CUI',
                    DB::raw("CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.ESTADO AS STATUS',
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'C.NOMBRE AS MODULO_CURSO',
                    'BC.CREATED_AT AS FECHA_INSCRIPCION',
                    'BC.ESTADO',
                    DB::raw("CAST(C.IMPULSATEC AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST('CURSO' AS VARCHAR2(50)) AS TIPO")
                )
                ->whereYear('BC.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                });


            $beneficiarios_actividades = DB::connection('gds')
                ->table('BENEFICIARIOS_ACTIVIDADES BA')
                ->join('BENEFICIARIOS B','BA.BENEFICIARIO_ID','=','B.ID')
                ->join('DETALLES_ACTIVIDADES DA','BA.DETALLE_ACTIVIDAD_ID','=','DA.ID')
                ->join('ACTIVIDADES A','DA.ACTIVIDAD_ID','=','A.ID')
                ->join('PROGRAMAS P','DA.PROGRAMA_ID','=','P.ID')
                ->join('DEPENDENCIAS D','P.DEPENDENCIA_ID','=','D.ID')
                ->join('TIPOS_ACTIVIDADES TA','DA.TIPO_ACTIVIDAD_ID','=','TA.ID')
                ->select(
                    'BA.ID AS INSCRIPCION_ID',
                    'B.CUI',
                    DB::raw("CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.ESTADO AS STATUS',
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'A.NOMBRE AS MODULO_CURSO',
                    'BA.CREATED_AT AS FECHA_INSCRIPCION',
                    'BA.ESTADO',
                    DB::raw("CAST('N' AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST(TA.NOMBRE AS VARCHAR2(50)) AS TIPO")
                )
                ->whereYear('BA.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                });

            $beneficiarios_inscritos = DB::connection('gds')
                ->table('BENEFICIARIOS_MODULOS BM')
                ->join('BENEFICIARIOS B','BM.BENEFICIARIO_ID','=','B.ID')
                ->join('MODULOS M','BM.MODULO_ID','=','M.ID')
                ->join('PROGRAMAS P','M.PROGRAMA_ID','=','P.ID')
                ->join('DEPENDENCIAS D','P.DEPENDENCIA_ID','=','D.ID')
                ->select(
                    'BM.ID AS INSCRIPCION_ID',
                    'B.CUI',
                    DB::raw("CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.ESTADO AS STATUS',
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'M.NOMBRE AS MODULO_CURSO',
                    'BM.CREATED_AT AS FECHA_INSCRIPCION',
                    'BM.ESTADO',
                    DB::raw("CAST('N' AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST('MODULO' AS VARCHAR2(50)) AS TIPO")
                )
                ->whereYear('BM.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                })
                ->unionAll($beneficiarios_cursos)
                ->unionAll($beneficiarios_actividades)
                ->orderBy('BENEFICIARIO')
                ->get();


            $count_beneficiarios_cursos = DB::connection('gds')
                ->table('BENEFICIARIOS_CURSOS BC')
                ->distinct()
                ->join('BENEFICIARIOS B','BC.BENEFICIARIO_ID','=','B.ID')
                ->join('DETALLES_CURSOS DC','BC.DETALLE_CURSO_ID','=','DC.ID')
                ->join('CURSOS C','DC.CURSO_ID','=','C.ID')
                ->join('PROGRAMAS P','DC.PROGRAMA_ID','=','P.ID')
                ->select('B.CUI')
                ->whereYear('BC.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                });
            
            $count_beneficiarios_actividades = DB::connection('gds')
                ->table('BENEFICIARIOS_ACTIVIDADES BA')
                ->distinct()
                ->join('BENEFICIARIOS B','BA.BENEFICIARIO_ID','=','B.ID')
                ->join('DETALLES_ACTIVIDADES DA','BA.DETALLE_ACTIVIDAD_ID','=','DA.ID')
                ->join('ACTIVIDADES A','DA.ACTIVIDAD_ID','=','A.ID')
                ->join('PROGRAMAS P','DA.PROGRAMA_ID','=','P.ID')
                ->select('B.CUI')
                
                ->whereYear('BA.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                });

            $total_beneficiario_unico = DB::connection('gds')
                ->table('BENEFICIARIOS_MODULOS BM')
                ->distinct()
                ->join('BENEFICIARIOS B','BM.BENEFICIARIO_ID','=','B.ID')
                ->join('MODULOS M','BM.MODULO_ID','=','M.ID')
                ->join('PROGRAMAS P','M.PROGRAMA_ID','=','P.ID')
                ->select('B.CUI')
                ->whereYear('BM.CREATED_AT',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                })
                ->union($count_beneficiarios_cursos)
                ->union($count_beneficiarios_actividades)
                ->count();

            return response([
                'total_beneficiario_unico' => $total_beneficiario_unico,
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

    public function get_modulos_cursos(Request $request) {
        $programa_id = $request->input('programa_id');
        $tipo = $request->input('tipo');

        try {

            if($tipo == 'modulo') {
                $modulos = modulos::where('programa_id',$programa_id)
                    ->get();

                return response($modulos);
            }

            $query = "
                SELECT
                    DC.ID,
                    C.NOMBRE,
                    DC.ESTADO
                FROM DETALLES_CURSOS DC
                LEFT JOIN CURSOS_MODULOS CM
                    ON DC.ID = CM.DETALLE_CURSO_ID
                INNER JOIN CURSOS C
                    ON DC.CURSO_ID = C.ID
                WHERE CM.MODULO_ID IS NULL
                AND DC.PROGRAMA_ID = ?
                ORDER BY C.NOMBRE ASC
            ";

            $cursos = DB::connection('gds')->select($query,[$programa_id]);
            return response($cursos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_beneficiarios_modulo_curso(Request $request) {

        $year = $request->input('year',date('Y'));
        $modulo_curso_id = $request->input('modulo_curso_id');
        $tipo = $request->input('tipo');

        try {

            $inscritos = [];

            if($tipo == 'modulo') {
                $inscritos = beneficiarios_modulos::whereHas('beneficiario',function($query) {
                        $query->where('estado','P');  
                    })
                    ->with(['beneficiario'])
                    ->where('modulo_id',$modulo_curso_id)
                    ->whereYear('created_at',$year)
                    ->get();
            } else {
                $inscritos = beneficiarios_cursos::whereHas('beneficiario',function($query) {
                        $query->where('estado','P');  
                    })  
                    ->with(['beneficiario'])
                    ->where('detalle_curso_id',$modulo_curso_id)
                    ->whereYear('created_at',$year)
                    ->get();
            }
            
            return response($inscritos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

}
