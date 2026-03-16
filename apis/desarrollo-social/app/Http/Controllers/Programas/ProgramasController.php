<?php

namespace App\Http\Controllers\Programas;

use App\Exports\GenerarReporteExcel;
use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\detalles_actividades;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use App\Models\adm_gds\programas;
use App\Models\adm_gds\tarifas_cursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProgramasController extends Controller
{
    public function index () {
        try {

            $perfil = strtolower(auth()->user()->perfil->nombre) == 'sysadmin' ? true : false;

            if ($perfil) {
                $programas = programas::with(['dependencia','modulos','escuela'])
                    ->latest('id')
                    ->get();
                return response($programas);
            }

            $programas = programas::whereHas('dependencia',function($query){
                    $query->where('dependencia_id',auth()->user()->dependencia_id);
                })
                ->with(['dependencia','modulos','escuela'])
                ->latest('id')
                ->get();
            return response($programas);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store (Request $request) {
        $request->validate([
            'nombre' => 'required_without:cursos.*.id|string|max:80',
            'descripcion' => 'nullable|string|max:255',
            'dependencia_id' => 'nullable|integer',
            'escuela_id' => 'required_if:dependencia_id,5,8|string|max:255',
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
                'escuela_id' => $request->escuela_id ?? null,
            ]);

            return response('Programa creado correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show (programas $programa) {
        try {
            return response($programa->load([
                    'dependencia',
                    'modulos.programa',
                    'modulos.sede',
                    'modulos.temporalidad',
                    'modulos.requisitos',
                    'modulos.cursos',
                    'modulos.tarifas'
                ])
            );  
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function update (Request $request, programas $programa) {
        $request->validate([
            'nombre' => 'required_without:cursos.*.id|string|max:80',
            'descripcion' => 'nullable|string|max:255',
            'dependencia_id' => 'required',
            'escuela_id' => 'nullable|string|max:255'
        ]);

        try {

            $programa->nombre = mb_strtoupper($request->nombre);
            $programa->descripcion = $request->descripcion ?? null;
            $programa->dependencia_id = $request->dependencia_id;
            $programa->estado = $request->estado;
            $programa->escuela_id = $request->escuela_id ?? null;

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

    public function get_modulos (programas $programa) {
        try {

            return response($programa->load([
                    'dependencia',
                    'modulos.programa',
                    'modulos.sede',
                    'modulos.temporalidad',
                    'modulos.requisitos',
                    'modulos.cursos',
                    'modulos.tarifas'
                ])->modulos
            );

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_cursos (int $programa, bool $all) {
        try {

            $programas = programas::with([
                'cursos' => function($query) use ($all){
                    $query->whereDoesntHave('modulo')
                    ->when(!$all, function($query) {
                        $query->where('estado','A');
                    })
                    ->orderByDesc('id');
                },
                'cursos.horarios',
                'cursos.sede',
                'cursos.instructores',
                'cursos.temporalidad',
                'cursos.curso',
                'cursos.tarifas',
                'cursos.programa'
            ])
            ->where('estado','A')
            ->where('id',$programa)
            ->first();

            return response($programas->cursos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_cursos(Request $request) {        
        try {

            $count_cursos = 0;

            foreach ($request->cursos as $index => $curso) {

                 $rules = [
                    'seccion' => 'nullable',
                ];

                // si NO viene con id, significa que es nuevo → reglas completas
                if (!isset($curso['id'])) {
                    $rules = array_merge($rules, [
                        'capacidad' => 'required|integer|min:1',
                        'modalidad' => 'required|string|in:PRESENCIAL,VIRTUAL,HIBRIDA',
                        'curso_id' => 'required|integer|exists:cursos,id',
                        'sede_id' => 'required|integer|exists:sedes,id',
                        'programa_id' => 'required|integer|exists:programas,id',
                        'temporalidad_id' => 'required|integer|exists:temporalidades,id',
                        'fecha_inicial' => 'required|date',
                        'fecha_final' => 'required|date|after:fecha_inicial',
                        'paga' => 'required|in:S,N',
                        'horarios' => 'required|array|min:1',
                    ]);

                    // si paga = S, se agregan reglas adicionales
                    if (isset($curso['paga']) && $curso['paga'] === 'S') {
                        $rules = array_merge($rules, [
                            'inscripcion' => 'nullable|numeric|min:0',
                            'tarifa_menor' => 'required|numeric|min:0',
                            'tarifa_mayor' => 'required|numeric|min:0',
                            'temporalidad_tarifa' => 'required|string',
                            'no_cuotas' => 'required|integer|min:1',
                            'mes_inicial' => 'required|date|date_format:Y-m',
                            // 'mes_final' => 'required|date|date_format:Y-m|after_or_equal:mes_inicial',
                        ]);
                    }
                }

                // validar cada curso por separado
                $validator = \Illuminate\Support\Facades\Validator::make($curso, $rules);

                if ($validator->fails()) {
                    // importante: mostrar el error indicando cuál curso falló
                    return response([
                        "message" => "Error en el curso #".($index+1),
                        "errors" => $validator->errors()
                    ], 422);
                }

                if(!isset($curso['id'])) {
                    $detalle_curso = detalles_cursos::create([
                        'seccion' => strtoupper($curso['seccion']) ?? null,
                        'capacidad' => $curso['capacidad'],
                        'modalidad' => $curso['modalidad'],
                        'curso_id' => $curso['curso_id'],
                        'sede_id' => $curso['sede_id'],
                        'programa_id' => $curso['programa_id'],
                        'temporalidad_id' => $curso['temporalidad_id'],
                        'fecha_inicial' => $curso['fecha_inicial'],
                        'fecha_final' => $curso['fecha_final'],
                        'publico' => 'S',
                        'estado' => 'A',
                        'paga' => $curso['paga'] ?? 'N',
                    ]);

                    $detalle_curso->horarios()->sync($curso['horarios']);
                    $detalle_curso->instructores()->sync($curso['instructores']);

                    if($curso['paga'] == 'S') {
                        if(isset($detalle_curso->id)) {
                            tarifas_cursos::create([
                                'tipo' => 'CURSO',
                                'curso_modulo_id' => $detalle_curso->id,
                                'inscripcion' => $curso['inscripcion'] ?? null,
                                'tarifa_menor' => $curso['tarifa_menor'],
                                'tarifa_mayor' => $curso['tarifa_mayor'],
                                'temporalidad' => $curso['temporalidad_tarifa'],
                                'no_cuotas' => $curso['no_cuotas'],
                                'mes_inicial' => $curso['mes_inicial'],
                                // 'mes_final' => $curso['mes_final'],
                            ]);
                        }
                    }

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
                ->join('SEDES S','DC.SEDE_ID','=','S.ID')
                ->join('CURSOS C','DC.CURSO_ID','=','C.ID')
                ->join('PROGRAMAS P','DC.PROGRAMA_ID','=','P.ID')
                ->leftJoin('ESCUELAS E','P.ESCUELA_ID','=','E.ID')
                ->join('DEPENDENCIAS D','P.DEPENDENCIA_ID','=','D.ID')
                ->select(
                    'BC.ID AS INSCRIPCION_ID',
                    'B.CUI',
                    DB::raw("ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.SEXO',
                    DB::raw("TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) AS EDAD"),
                    'B.ESTADO AS STATUS',
                    'E.NOMBRE AS ESCUELA',
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'DC.ID AS ID_MODULO_CURSO', 
                    'C.NOMBRE AS MODULO_CURSO',
                    'DC.SECCION',
                    'S.NOMBRE AS SEDE',
                    'BC.ANIO_INSCRIPCION AS ANIO_INSCRIPCION',
                    'BC.ESTADO',
                    DB::raw("CAST(C.IMPULSATEC AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST('CURSO' AS VARCHAR2(50)) AS TIPO"),
                    DB::raw("TO_CHAR(BC.CREATED_AT,'YYYY-MM-DD') AS FECHA_REGISTRO"),
                )
                ->where('BC.ANIO_INSCRIPCION',$year)
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
                    DB::raw("ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.SEXO',
                    DB::raw("TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) AS EDAD"),
                    'B.ESTADO AS STATUS',
                    DB::raw("NULL AS ESCUELA"),
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'DA.ID AS ID_MODULO_CURSO',
                    'A.NOMBRE AS MODULO_CURSO',
                    DB::raw("NULL AS SECCION"),
                    DB::raw("NULL AS SEDE"),
                    // DB::raw("EXTRACT(YEAR FROM BA.CREATED_AT) AS ANIO_INSCRIPCION"),
                    'BA.ANIO_INSCRIPCION',
                    'BA.ESTADO',
                    DB::raw("CAST('N' AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST(TA.NOMBRE AS VARCHAR2(50)) AS TIPO"),
                    DB::raw("TO_CHAR(BA.CREATED_AT,'YYYY-MM-DD') AS FECHA_REGISTRO"),
                )
                ->where('BA.ANIO_INSCRIPCION',$year)
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
                ->join('SEDES S','M.SEDE_ID','=','S.ID')
                ->join('PROGRAMAS P','M.PROGRAMA_ID','=','P.ID')
                ->leftJoin('ESCUELAS E','P.ESCUELA_ID','=','E.ID')
                ->join('DEPENDENCIAS D','P.DEPENDENCIA_ID','=','D.ID')
                ->select(
                    'BM.ID AS INSCRIPCION_ID',
                    'B.CUI',
                    DB::raw("ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE,B.SEGUNDO_NOMBRE,B.PRIMER_APELLIDO,B.SEGUNDO_APELLIDO) AS BENEFICIARIO"),
                    'B.CORREO',
                    'B.CELULAR',
                    'B.SEXO',
                    DB::raw("TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) AS EDAD"),
                    'B.ESTADO AS STATUS',
                    'E.NOMBRE AS ESCUELA',
                    'P.NOMBRE AS PROGRAMA',
                    'D.NOMBRE AS DEPENDENCIA',
                    'M.ID AS ID_MODULO_CURSO',
                    'M.NOMBRE AS MODULO_CURSO',
                    'M.SECCION',
                    'S.NOMBRE AS SEDE',
                    'BM.ANIO_INSCRIPCION AS ANIO_INSCRIPCION',
                    'BM.ESTADO',
                    DB::raw("CAST('N' AS VARCHAR2(1)) AS IMPULSATEC"),
                    DB::raw("CAST('MODULO' AS VARCHAR2(50)) AS TIPO"),
                    DB::raw("TO_CHAR(BM.CREATED_AT,'YYYY-MM-DD') AS FECHA_REGISTRO"),
                )
                ->where('BM.ANIO_INSCRIPCION',$year)
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
                ->where('BC.ANIO_INSCRIPCION',$year)
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
                
                ->where('BA.ANIO_INSCRIPCION',$year)
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
                ->where('BM.ANIO_INSCRIPCION',$year)
                ->when(!empty($programa_id),function($query) use ($programa_id){
                    return $query->where('P.ID',$programa_id);
                })
                ->when(!$perfil, function($query) use ($dependencia_id){
                    return $query->where('P.DEPENDENCIA_ID',$dependencia_id);
                })
                ->union($count_beneficiarios_cursos)
                ->union($count_beneficiarios_actividades)
                ->get();

            return response([
                'total_beneficiario_unico' => $total_beneficiario_unico->count(),
                'beneficiarios_inscritos' => $beneficiarios_inscritos
            ]);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_actividades (int $programa_id) {
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
                FROM ADM_GDS.DETALLES_ACTIVIDADES DA
                    INNER JOIN ADM_GDS.PROGRAMAS P
                            ON DA.PROGRAMA_ID = P.ID
                    LEFT JOIN ADM_GDS.ZONAS Z
                            ON DA.ZONA_ID = Z.ID
                    LEFT JOIN ADM_GDS.DISTRITOS D
                            ON DA.DISTRITO_ID = D.ID
                    INNER JOIN ADM_GDS.ACTIVIDADES A
                            ON DA.ACTIVIDAD_ID = A.ID
                    LEFT JOIN ADM_GDS.TIPOS_ACTIVIDADES TA
                            ON DA.TIPO_ACTIVIDAD_ID = TA.ID
                    LEFT JOIN ADM_GDS.ESTADOS_ACTIVIDADES EA
                            ON DA.ESTADO_ACTIVIDAD_ID = EA.ID
                WHERE DA.PROGRAMA_ID = ?
                ORDER BY DA.ID DESC        
            ";

            $actividades_programa = DB::connection('gds')->select($query,[$programa_id]);
            
            return response($actividades_programa);  

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_actividades(Request $request) {
        $request->validate([
            'actividades' => 'required_without:cursos.*.id|array'
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
                FROM ADM_GDS.DETALLES_CURSOS DC
                LEFT JOIN ADM_GDS.CURSOS_MODULOS CM
                    ON DC.ID = CM.DETALLE_CURSO_ID
                INNER JOIN ADM_GDS.CURSOS C
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
                    ->where('anio_inscripcion',$year)
                    ->get();
            } else {
                $inscritos = beneficiarios_cursos::whereHas('beneficiario',function($query) {
                        $query->where('estado','P');  
                    })  
                    ->with(['beneficiario'])
                    ->where('detalle_curso_id',$modulo_curso_id)
                    ->where('anio_inscripcion',$year)
                    ->get();
            }
            
            return response($inscritos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function programas_escuela(int $escuela_id) {
        try {

            $programas_escuela = programas::with(['modulos'])
                ->where('estado','A')
                ->where('escuela_id',$escuela_id)
                ->get();

            return response([
                'message' => 'Se obtuvieron los programas exitosamente',
                'programas_escuela' => $programas_escuela
            ]);

        } catch (\Throwable $th) {
            return response([
                'error' => 'Error al obtener los programas por escuela',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function generar_reporte(Request $request) {
        $request->validate([
            'anio_inscripcion' => 'required|digits:4',
            'programas' => 'required|array'
        ]);

        $programas = implode(",",$request->programas);
        $anio_inscripcion = $request->anio_inscripcion;

        try {
            
            $query="
                SELECT
                    B.ID ID_BENEFICIARIO,
                    B.CUI,
                    B.INTERLOCUTOR,
                    B.PASAPORTE,
                    ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE, B.SEGUNDO_NOMBRE, B.PRIMER_APELLIDO, B.SEGUNDO_APELLIDO) BENEFICIARIO,
                    B.SEXO,
                    TO_CHAR(B.FECHA_NACIMIENTO,'DD-MM-YYYY') FECHA_NACIMIENTO,
                    TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) EDAD,
                    B.CELULAR,
                    B.CORREO,
                    EC.NOMBRE ESTADO_CIVIL,
                    ET.NOMBRE ETNIA,
                    DEP.NOMBRE DEPARTAMENTO,
                    M.NOMBRE MUNICIPIO,
                    D.ZONA_ID ZONA,
                    GH.NOMBRE GRUPO_HABITACIONAL,
                    GZ.DESCRIPCION,
                    D.DIRECCION,
                    TO_CHAR(B.CREATED_AT,'DD-MM-YYYY') FECHA_PRIMER_REGISTRO,
                    DM.ENFERMEDADES_ALERGIAS,
                    DM.MEDICAMENTOS,
                    DM.DOSIS,
                    TS.NOMBRE TIPO_SANGRE,
                    DA.ESTABLECIMIENTO,
                    DA.TITULO_CARRERA,
                    ESCO.NOMBRE ESCOLARIDAD,
                    DA.TIPO TIPO_ESTABLECIMIENTO,
                    RESP.CUI RESPONSABLE_CUI,
                    RESP.NOMBRES RESPONSABLE_NOMBRES,
                    RESP.APELLIDOS RESPONSABLE_APELLIDOS,
                    TO_CHAR(RESP.FECHA_NACIMIENTO, 'DD-MM-YYYY') RESPONSABLE_FECHA_NACIMIENTO,
                    RESP.CELULAR RESPONSABLE_CELULAR,
                    RESP.EMAIL RESPONSABLE_CORREO,
                    PARENT.NOMBRE RESPONSABLE_PARENTESCO,
                    RESP.SEXO RESPONSABLE_SEXO,
                    RESP.DIRECCION RESPONSABLE_DIRECCION,
                    RESP.ZONA_ID RESPONSABLE_ZONA,
                    EMER.CUI EMERGENCIA_CUI,
                    EMER.NOMBRES EMERGENCIA_NOMBRES,
                    EMER.APELLIDOS EMERGENCIA_APELLIDOS,
                    TO_CHAR(EMER.FECHA_NACIMIENTO,'DD-MM-YYYY') EMERGENCIA_FECHA_NACIMIENTO,
                    EMER.CELULAR EMERGENCIA_CELULAR,
                    EMER.EMAIL EMERGENCIA_CORREO,
                    PARENT_EMER.NOMBRE EMERGENCIA_PARENTESCO,
                    EMER.SEXO EMERGENCIA_SEXO,
                    EMER.DIRECCION EMERGENCIA_DIRECCION,
                    EMER.ZONA_ID EMERGENCIA_ZONA,
                    BA.ANIO_INSCRIPCION,
                    CASE WHEN DA.TIPO_ACTIVIDAD_ID = 1 THEN 'SERVICIO' ELSE 'EVENTO' END AS TIPO_ACTIVIDAD,
                    BA.ID ID_INSCRIPCION,
                    TO_CHAR(BA.CREATED_AT,'YYYY-MM-DD') FECHA_ASIGNACION,
                    BA.ESTADO ESTADO_INSCRIPCION,
                    DEPEN.NOMBRE DEPENDENCIA,
                    '' ESCUELA,
                    PROG.NOMBRE PROGRAMA,
                    DA.ID ID_ASIGNACION,
                    ACT.NOMBRE ACTIVIDAD,
                    'N' IMPULSATEC,
                    '' SECCION,
                    '' MODALIDAD,
                    '' TEMPORALIDAD,
                    '' SEDE,
                    DA.DIRECCION ACTIVIDAD_DIRECCION,
                    DA.ZONA_ID ACTIVIDAD_ZONA,
                    '' PUBLICO,
                    '' PAGA
                FROM ADM_GDS.BENEFICIARIOS_ACTIVIDADES BA
                    INNER JOIN ADM_GDS.BENEFICIARIOS B
                        ON BENEFICIARIO_ID = B.ID
                    LEFT JOIN (
                        SELECT
                            D.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.DOMICILIOS D
                    ) D
                        ON B.ID = D.BENEFICIARIO_ID AND D.RNK = 1
                    LEFT JOIN ADM_GDS.MUNICIPIOS M
                        ON D.MUNICIPIO_ID = M.ID
                    LEFT JOIN ADM_GDS.DEPARTAMENTOS DEP
                        ON M.DEPARTAMENTO_ID = DEP.ID
                    LEFT JOIN ADM_GDS.ESTADOS_CIVILES EC
                        ON B.ESTADO_CIVIL_ID = EC.ID
                    LEFT JOIN ADM_GDS.ETNIAS ET
                        ON B.ETNIA_ID = ET.ID
                    LEFT JOIN ADM_GDS.GRUPOS_ZONAS GZ
                        ON D.GRUPO_ZONA_ID = GZ.ID
                    LEFT JOIN ADM_GDS.GRUPOS_HABITACIONALES GH
                        ON GZ.GRUPO_HABITACIONAL_ID = GH.ID
                    LEFT JOIN ADM_GDS.DATOS_MEDICOS DM
                        ON DM.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.TIPOS_SANGRE TS
                        ON DM.TIPO_SANGRE_ID = TS.ID
                    LEFT JOIN ADM_GDS.DATOS_ACADEMICOS DA
                        ON DA.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.ESCOLARIDADES ESCO
                        ON DA.ESCOLARIDAD_ID = ESCO.ID
                    LEFT JOIN (
                        SELECT
                            RESP.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES RESP
                        WHERE RESP.CATEGORIA = 'R'
                    ) RESP
                        ON B.ID = RESP.BENEFICIARIO_ID AND RESP.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT
                        ON RESP.PARENTESCO_ID = PARENT.ID
                    LEFT JOIN (
                        SELECT
                            EMER.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES EMER
                        WHERE EMER.CATEGORIA = 'E'
                    ) EMER
                        ON B.ID = EMER.BENEFICIARIO_ID AND EMER.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT_EMER
                        ON EMER.PARENTESCO_ID = PARENT_EMER.ID
                    INNER JOIN ADM_GDS.DETALLES_ACTIVIDADES DA
                        ON BA.DETALLE_ACTIVIDAD_ID = DA.ID
                    INNER JOIN ADM_GDS.ACTIVIDADES ACT
                        ON DA.ACTIVIDAD_ID = ACT.ID
                    INNER JOIN ADM_GDS.PROGRAMAS PROG
                        ON DA.PROGRAMA_ID = PROG.ID
                    INNER JOIN ADM_GDS.DEPENDENCIAS DEPEN
                        ON PROG.DEPENDENCIA_ID = DEPEN.ID
                WHERE BA.ANIO_INSCRIPCION = $anio_inscripcion
                AND PROG.ID IN($programas)

                UNION

                SELECT
                    B.ID ID_BENEFICIARIO,
                    B.CUI,
                    B.INTERLOCUTOR,
                    B.PASAPORTE,
                    ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE, B.SEGUNDO_NOMBRE, B.PRIMER_APELLIDO, B.SEGUNDO_APELLIDO) BENEFICIARIO,
                    B.SEXO,
                    TO_CHAR(B.FECHA_NACIMIENTO,'DD-MM-YYYY') FECHA_NACIMIENTO,
                    TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) EDAD,
                    B.CELULAR,
                    B.CORREO,
                    EC.NOMBRE ESTADO_CIVIL,
                    ET.NOMBRE ETNIA,
                    DEP.NOMBRE DEPARTAMENTO,
                    M.NOMBRE MUNICIPIO,
                    D.ZONA_ID ZONA,
                    GH.NOMBRE GRUPO_HABITACIONAL,
                    GZ.DESCRIPCION,
                    D.DIRECCION,
                    TO_CHAR(B.CREATED_AT,'DD-MM-YYYY') FECHA_PRIMER_REGISTRO,
                    DM.ENFERMEDADES_ALERGIAS,
                    DM.MEDICAMENTOS,
                    DM.DOSIS,
                    TS.NOMBRE TIPO_SANGRE,
                    DA.ESTABLECIMIENTO,
                    DA.TITULO_CARRERA,
                    ESCO.NOMBRE ESCOLARIDAD,
                    DA.TIPO TIPO_ESTABLECIMIENTO,
                    RESP.CUI RESPONSABLE_CUI,
                    RESP.NOMBRES RESPONSABLE_NOMBRES,
                    RESP.APELLIDOS RESPONSABLE_APELLIDOS,
                    TO_CHAR(RESP.FECHA_NACIMIENTO, 'DD-MM-YYYY') RESPONSABLE_FECHA_NACIMIENTO,
                    RESP.CELULAR RESPONSABLE_CELULAR,
                    RESP.EMAIL RESPONSABLE_CORREO,
                    PARENT.NOMBRE RESPONSABLE_PARENTESCO,
                    RESP.SEXO RESPONSABLE_SEXO,
                    RESP.DIRECCION RESPONSABLE_DIRECCION,
                    RESP.ZONA_ID RESPONSABLE_ZONA,
                    EMER.CUI EMERGENCIA_CUI,
                    EMER.NOMBRES EMERGENCIA_NOMBRES,
                    EMER.APELLIDOS EMERGENCIA_APELLIDOS,
                    TO_CHAR(EMER.FECHA_NACIMIENTO,'DD-MM-YYYY') EMERGENCIA_FECHA_NACIMIENTO,
                    EMER.CELULAR EMERGENCIA_CELULAR,
                    EMER.EMAIL EMERGENCIA_CORREO,
                    PARENT_EMER.NOMBRE EMERGENCIA_PARENTESCO,
                    EMER.SEXO EMERGENCIA_SEXO,
                    EMER.DIRECCION EMERGENCIA_DIRECCION,
                    EMER.ZONA_ID EMERGENCIA_ZONA,
                    BC.ANIO_INSCRIPCION,
                    'CURSO' TIPO_ACTIVIDAD,
                    BC.ID ID_INSCRIPCION,
                    TO_CHAR(BC.CREATED_AT,'YYYY-MM-DD') FECHA_ASIGNACION,
                    BC.ESTADO ESTADO_INSCRIPCION,
                    DEPEN.NOMBRE DEPENDENCIA,
                    ESC.NOMBRE ESCUELA,
                    PROG.NOMBRE PROGRAMA,
                    DC.ID ID_ASIGNACION,
                    C.NOMBRE CURSO,
                    C.IMPULSATEC,
                    DC.SECCION,
                    DC.MODALIDAD,
                    TEMP.NOMBRE TEMPORALIDAD,
                    S.NOMBRE SEDE,
                    S.DIRECCION SEDE_DIRECCION,
                    S.ZONA_ID SEDE_ZONA,
                    DC.PUBLICO,
                    DC.PAGA
                FROM ADM_GDS.BENEFICIARIOS_CURSOS BC
                    INNER JOIN ADM_GDS.BENEFICIARIOS B
                        ON BENEFICIARIO_ID = B.ID
                    LEFT JOIN (
                        SELECT
                            D.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.DOMICILIOS D
                    ) D
                        ON B.ID = D.BENEFICIARIO_ID AND D.RNK = 1
                    LEFT JOIN ADM_GDS.MUNICIPIOS M
                        ON D.MUNICIPIO_ID = M.ID
                    LEFT JOIN ADM_GDS.DEPARTAMENTOS DEP
                        ON M.DEPARTAMENTO_ID = DEP.ID
                    LEFT JOIN ADM_GDS.ESTADOS_CIVILES EC
                        ON B.ESTADO_CIVIL_ID = EC.ID
                    LEFT JOIN ADM_GDS.ETNIAS ET
                        ON B.ETNIA_ID = ET.ID
                    LEFT JOIN ADM_GDS.GRUPOS_ZONAS GZ
                        ON D.GRUPO_ZONA_ID = GZ.ID
                    LEFT JOIN ADM_GDS.GRUPOS_HABITACIONALES GH
                        ON GZ.GRUPO_HABITACIONAL_ID = GH.ID
                    LEFT JOIN ADM_GDS.DATOS_MEDICOS DM
                        ON DM.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.TIPOS_SANGRE TS
                        ON DM.TIPO_SANGRE_ID = TS.ID
                    LEFT JOIN ADM_GDS.DATOS_ACADEMICOS DA
                        ON DA.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.ESCOLARIDADES ESCO
                        ON DA.ESCOLARIDAD_ID = ESCO.ID
                    LEFT JOIN (
                        SELECT
                            RESP.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES RESP
                        WHERE RESP.CATEGORIA = 'R'
                    ) RESP
                        ON B.ID = RESP.BENEFICIARIO_ID AND RESP.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT
                        ON RESP.PARENTESCO_ID = PARENT.ID
                    LEFT JOIN (
                        SELECT
                            EMER.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES EMER
                        WHERE EMER.CATEGORIA = 'E'
                    ) EMER
                        ON B.ID = EMER.BENEFICIARIO_ID AND EMER.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT_EMER
                        ON EMER.PARENTESCO_ID = PARENT_EMER.ID
                    INNER JOIN ADM_GDS.DETALLES_CURSOS DC
                        ON BC.DETALLE_CURSO_ID = DC.ID
                    INNER JOIN ADM_GDS.CURSOS C
                        ON DC.CURSO_ID = C.ID
                    INNER JOIN ADM_GDS.SEDES S
                        ON DC.SEDE_ID = S.ID
                    INNER JOIN ADM_GDS.TEMPORALIDADES TEMP
                        ON DC.TEMPORALIDAD_ID = TEMP.ID
                    INNER JOIN ADM_GDS.PROGRAMAS PROG
                        ON DC.PROGRAMA_ID = PROG.ID
                    LEFT JOIN ADM_GDS.ESCUELAS ESC
                        ON PROG.ESCUELA_ID = ESC.ID
                    INNER JOIN ADM_GDS.DEPENDENCIAS DEPEN
                        ON PROG.DEPENDENCIA_ID = DEPEN.ID
                WHERE BC.ANIO_INSCRIPCION = $anio_inscripcion
                AND PROG.ID IN($programas)

                UNION

                SELECT
                    B.ID ID_BENEFICIARIO,
                    B.CUI,
                    B.INTERLOCUTOR,
                    B.PASAPORTE,
                    ADM_GDS.CONCATENARNOMBRES(B.PRIMER_NOMBRE, B.SEGUNDO_NOMBRE, B.PRIMER_APELLIDO, B.SEGUNDO_APELLIDO) BENEFICIARIO,
                    B.SEXO,
                    TO_CHAR(B.FECHA_NACIMIENTO,'DD-MM-YYYY') FECHA_NACIMIENTO,
                    TRUNC(MONTHS_BETWEEN(SYSDATE, B.FECHA_NACIMIENTO) / 12) EDAD,
                    B.CELULAR,
                    B.CORREO,
                    EC.NOMBRE ESTADO_CIVIL,
                    ET.NOMBRE ETNIA,
                    DEP.NOMBRE DEPARTAMENTO,
                    M.NOMBRE MUNICIPIO,
                    D.ZONA_ID ZONA,
                    GH.NOMBRE GRUPO_HABITACIONAL,
                    GZ.DESCRIPCION,
                    D.DIRECCION,
                    TO_CHAR(B.CREATED_AT,'DD-MM-YYYY') FECHA_PRIMER_REGISTRO,
                    DM.ENFERMEDADES_ALERGIAS,
                    DM.MEDICAMENTOS,
                    DM.DOSIS,
                    TS.NOMBRE TIPO_SANGRE,
                    DA.ESTABLECIMIENTO,
                    DA.TITULO_CARRERA,
                    ESCO.NOMBRE ESCOLARIDAD,
                    DA.TIPO TIPO_ESTABLECIMIENTO,
                    RESP.CUI RESPONSABLE_CUI,
                    RESP.NOMBRES RESPONSABLE_NOMBRES,
                    RESP.APELLIDOS RESPONSABLE_APELLIDOS,
                    TO_CHAR(RESP.FECHA_NACIMIENTO, 'DD-MM-YYYY') RESPONSABLE_FECHA_NACIMIENTO,
                    RESP.CELULAR RESPONSABLE_CELULAR,
                    RESP.EMAIL RESPONSABLE_CORREO,
                    PARENT.NOMBRE RESPONSABLE_PARENTESCO,
                    RESP.SEXO RESPONSABLE_SEXO,
                    RESP.DIRECCION RESPONSABLE_DIRECCION,
                    RESP.ZONA_ID RESPONSABLE_ZONA,
                    EMER.CUI EMERGENCIA_CUI,
                    EMER.NOMBRES EMERGENCIA_NOMBRES,
                    EMER.APELLIDOS EMERGENCIA_APELLIDOS,
                    TO_CHAR(EMER.FECHA_NACIMIENTO,'DD-MM-YYYY') EMERGENCIA_FECHA_NACIMIENTO,
                    EMER.CELULAR EMERGENCIA_CELULAR,
                    EMER.EMAIL EMERGENCIA_CORREO,
                    PARENT_EMER.NOMBRE EMERGENCIA_PARENTESCO,
                    EMER.SEXO EMERGENCIA_SEXO,
                    EMER.DIRECCION EMERGENCIA_DIRECCION,
                    EMER.ZONA_ID EMERGENCIA_ZONA,
                    BM.ANIO_INSCRIPCION,
                    'MODULO' TIPO_ACTIVIDAD,
                    BM.ID ID_INSCRIPCION,
                    TO_CHAR(BM.CREATED_AT,'YYYY-MM-DD') FECHA_ASIGNACION,
                    BM.ESTADO ESTADO_INSCRIPCION,
                    DEPEN.NOMBRE DEPENDENCIA,
                    ESC.NOMBRE ESCUELA,
                    PROG.NOMBRE PROGRAMA,
                    MOD.ID ID_ASIGNACION,
                    MOD.NOMBRE MODULO,
                    'N' IMPULSATEC,
                    MOD.SECCION,
                    MOD.MODALIDAD,
                    TEMP.NOMBRE TEMPORALIDAD,
                    S.NOMBRE SEDE,
                    S.DIRECCION SEDE_DIRECCION,
                    S.ZONA_ID SEDE_ZONA,
                    MOD.PUBLICO,
                    MOD.PAGA
                FROM ADM_GDS.BENEFICIARIOS_MODULOS BM
                    INNER JOIN ADM_GDS.BENEFICIARIOS B
                        ON BENEFICIARIO_ID = B.ID
                    LEFT JOIN (
                        SELECT
                            D.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.DOMICILIOS D
                    ) D
                        ON B.ID = D.BENEFICIARIO_ID AND D.RNK = 1
                    LEFT JOIN ADM_GDS.MUNICIPIOS M
                        ON D.MUNICIPIO_ID = M.ID
                    LEFT JOIN ADM_GDS.DEPARTAMENTOS DEP
                        ON M.DEPARTAMENTO_ID = DEP.ID
                    LEFT JOIN ADM_GDS.ESTADOS_CIVILES EC
                        ON B.ESTADO_CIVIL_ID = EC.ID
                    LEFT JOIN ADM_GDS.ETNIAS ET
                        ON B.ETNIA_ID = ET.ID
                    LEFT JOIN ADM_GDS.GRUPOS_ZONAS GZ
                        ON D.GRUPO_ZONA_ID = GZ.ID
                    LEFT JOIN ADM_GDS.GRUPOS_HABITACIONALES GH
                        ON GZ.GRUPO_HABITACIONAL_ID = GH.ID
                    LEFT JOIN ADM_GDS.DATOS_MEDICOS DM
                        ON DM.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.TIPOS_SANGRE TS
                        ON DM.TIPO_SANGRE_ID = TS.ID
                    LEFT JOIN ADM_GDS.DATOS_ACADEMICOS DA
                        ON DA.BENEFICIARIO_ID = B.ID
                    LEFT JOIN ADM_GDS.ESCOLARIDADES ESCO
                        ON DA.ESCOLARIDAD_ID = ESCO.ID
                    LEFT JOIN (
                        SELECT
                            RESP.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES RESP
                        WHERE RESP.CATEGORIA = 'R'
                    ) RESP
                        ON B.ID = RESP.BENEFICIARIO_ID AND RESP.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT
                        ON RESP.PARENTESCO_ID = PARENT.ID
                    LEFT JOIN (
                        SELECT
                            EMER.*,
                            ROW_NUMBER() OVER (PARTITION BY BENEFICIARIO_ID ORDER BY ID DESC) as RNK
                        FROM ADM_GDS.RESPONSABLES EMER
                        WHERE EMER.CATEGORIA = 'E'
                    ) EMER
                        ON B.ID = EMER.BENEFICIARIO_ID AND EMER.RNK = 1
                    LEFT JOIN ADM_GDS.PARENTESCOS PARENT_EMER
                        ON EMER.PARENTESCO_ID = PARENT_EMER.ID
                    INNER JOIN ADM_GDS.MODULOS MOD
                        ON BM.MODULO_ID = MOD.ID
                    INNER JOIN ADM_GDS.SEDES S
                        ON MOD.SEDE_ID = S.ID
                    INNER JOIN ADM_GDS.TEMPORALIDADES TEMP
                        ON MOD.TEMPORALIDAD_ID = TEMP.ID
                    INNER JOIN ADM_GDS.PROGRAMAS PROG
                        ON MOD.PROGRAMA_ID = PROG.ID
                    LEFT JOIN ADM_GDS.ESCUELAS ESC
                        ON PROG.ESCUELA_ID = ESC.ID
                    INNER JOIN ADM_GDS.DEPENDENCIAS DEPEN
                        ON PROG.DEPENDENCIA_ID = DEPEN.ID
                WHERE BM.ANIO_INSCRIPCION = $anio_inscripcion
                AND PROG.ID IN($programas)
            ";

            $rows = DB::connection('gds')->select($query);
            $columns = collect($rows)->first();

            if($rows) {
                return Excel::download(new GenerarReporteExcel($rows,$columns),'export.xlsx');
            }

            return response([
                'message' => 'No hay se genero por que no hay informacion.'
            ],200);

        } catch (\Throwable $th) {
            return response([
                'error' => $th->getMessage(),
                'message' => 'Error al realizar la consulta.',
            ]);
        }
    }

}
