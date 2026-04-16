<?php

namespace App\Http\Controllers\Beneficiarios;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiarioUnicoResource;
use App\Http\Resources\RenapConsultaResource;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\bitacora;
use App\Models\Muni\TbBeneficiarioUnico;
use App\Rules\ValidateCui;
use App\Traits\TraitBeneficiarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiariosController extends Controller
{
    use TraitBeneficiarios;

    public function index(Request $request) {

        $per_page = $request->input('per_page',10);

        try {

            $beneficiarios = beneficiarios::advancedFilter($request)
                ->paginate($per_page);

            if ($beneficiarios->isEmpty() && $request->searching['search']) {
                $fallbackQuery = beneficiarios::whereRaw(
                    "LOWER(ADM_GDS.CONCATENARNOMBRES(PRIMER_NOMBRE, SEGUNDO_NOMBRE, PRIMER_APELLIDO, SEGUNDO_APELLIDO)) LIKE LOWER(?)",
                    ["%{$request->searching['search']}%"]
                );

                $beneficiarios = $fallbackQuery->paginate($per_page);
            }

            return response($beneficiarios);
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function show(beneficiarios $beneficiario) {
        try {
            return response(
                $beneficiario->load([
                    'domicilio',
                    'datos_medicos',
                    'datos_academicos',
                    'responsable',
                    'emergencia'
                ])
            );
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store(Request $request) {

        DB::connection('gds')->beginTransaction();
        
        try {
            
            $beneficiario = $this->storeBeneficiario($request);

            if($beneficiario) {

                $this->storeDomicilio($request,$beneficiario->id);

                if (
                    isset($request->datos_medicos['tipo_sangre_id']) ||
                    isset($request->datos_medicos['enfermedades_alergias'])
                ){

                    $this->storeDatosMedicos($request,$beneficiario->id);
                }

                if (
                    isset($request->datos_academicos['tipo'])
                ){

                    $this->storeDatosAcademicos($request,$beneficiario->id);
                }

                if($request->edad < 18 ) {
                    if (
                        isset($request->responsable['nombres']) &&
                        isset($request->responsable['apellidos']) &&
                        isset($request->responsable['fecha_nacimiento']) &&
                        isset($request->responsable['cui'])
                    ){
    
                        $this->storeResponsable($request,$beneficiario->id);
                    }
                }

                if (
                    isset($request->emergencia['nombres']) &&
                    isset($request->emergencia['apellidos']) &&
                    isset($request->emergencia['celular'])
                ){

                    $this->storeEmergencia($request, $beneficiario->id);
                }
                
                bitacora::create([
                    'accion' => bitacora::$acciones[1],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE CREO BENEFICIARIO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $beneficiario->id,
                ]);
            }



            if(!empty($this->bagValidations)){
                DB::connection('gds')->rollBack();
                return response([
                    'message' => 'Hay campos que no cumplen con las validaciones',
                    'errors' => $this->bagValidations
                ],422);
            }

            DB::connection('gds')->commit();
            
            return response('Se ha almacenado los datos correctamente');

        } catch (\Throwable $th) {

            DB::connection('gds')->rollBack();
            return response($th->getMessage());

        }
    }

    public function update(Request $request, beneficiarios $beneficiario) {

        DB::connection('gds')->beginTransaction();
        
        try {
            
            $this->updateBeneficiario($request, $beneficiario);

            if (!is_null($beneficiario->domicilio)) {
                $this->updateDomicilio($request, $beneficiario);
            } else {
                $this->storeDomicilio($request,$beneficiario->id);
            }

            if(!is_null($beneficiario->datos_academicos)) {
                $this->updateDatosAcademicos($request,$beneficiario);
            } else {
                if (
                    isset($request->datos_academicos['tipo']) ||
                    isset($request->datos_academicos['escolaridad_id'])
                ) {
                    $this->storeDatosAcademicos($request, $beneficiario->id);
                }
            }


            if(!is_null($beneficiario->datos_medicos)) {
                $this->updateDatosMedicos($request, $beneficiario);
            } else {
                if(
                    isset($request->datos_medicos['tipo_sangre_id']) ||
                    isset($request->datos_medicos['enfermedades_alergias'])
                ){
                    $this->storeDatosMedicos($request, $beneficiario->id);
                }
            }

            if(isset($request->edad) && (intval($request->edad) < 18)) {

                if(!is_null($beneficiario->responsable)) {
                    $this->updateResponsable($request, $beneficiario);
                } else {
                    if(
                        isset($request->responsable['nombres']) &&
                        isset($request->responsable['apellidos']) &&
                        isset($request->responsable['cui']) &&
                        isset($request->responsable['fecha_nacimiento'])
                    ) {
                        $this->storeResponsable($request, $beneficiario->id);
                    }
                }
            }

            if(!is_null($beneficiario->emergencia)) {
                $this->updateEmergencia($request, $beneficiario);
            } else {
                if (
                    isset($request->emergencia['nombres']) &&
                    isset($request->emergencia['apellidos']) &&
                    isset($request->emergencia['celular'])
                ) {
                    $this->storeEmergencia($request, $beneficiario->id);
                }
            }

            bitacora::create([
                'accion' => bitacora::$acciones[0],
                'tabla' => 'BENEFICIARIOS',
                'descripcion' => 'SE MODIFICO INFORMACION DEL BENEFICIARIO',
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $beneficiario->id,
            ]);
            

            if(!empty($this->bagValidations)){
                DB::connection('gds')->rollBack();
                return response([
                    'message' => 'Hay campos que no cumplen con las validaciones',
                    'errors' => $this->bagValidations
                ],422);
            }

            DB::connection('gds')->commit();
            
            return response('Se ha almacenado los datos correctamente');

        } catch (\Throwable $th) {

            DB::connection('gds')->rollBack();
            return response($th->getMessage(),422);

        }
    }

    public function destroy(beneficiarios $beneficiario) {
        try {
            $beneficiario->deleted_at = now();
            $beneficiario->save();

            bitacora::create([
                    'accion' => bitacora::$acciones[17],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE DESHABILITO AL BENEFICIARIO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $beneficiario->id,
                ]);

            return response('Beneficiario desactivado correctamente.');
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function consultaBackUp(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui ],
        ]);

        try {

            $beneficiarioUnico = beneficiarios::where('cui',$request->cui)->first();
            
            if(!$beneficiarioUnico) {

                $beneficiarioUnico = TbBeneficiarioUnico::where('cui',$request->cui)->first();

                if($beneficiarioUnico) {
                    return response([
                        'message' => 'Se encontro información en la base de datos antigua',
                        'success' => true,
                        'data' => BeneficiarioUnicoResource::make($beneficiarioUnico),
                        'code' => 4,
                    ]);
                }

                $beneficiarioUnico = $this->verifyRenapCui($request->cui);

                if($beneficiarioUnico['data']) {
                    return response([
                        'message' => 'Se consulto en RENAP',
                        'success' => true,
                        'data' => RenapConsultaResource::make($beneficiarioUnico['data']),
                        'code' => 5,
                    ]);
                } else {
                    return response([
                        'message' => 'Cui invalido',
                        'success' => false,
                        'data' => [],
                        'code' => 6
                    ]);
                }
            }


            return response([
                'message' => 'El cui ya existe en la base de datos actual',
                'success' => false,
                'data' => [],
                'code' => 2
            ]); 
        
        } catch (\Throwable $th) {
            
            return response([
                'message' =>  $th->getMessage(),
                'success' => false,
                'data' => [],
                'code' => $th->getMessage() === 'Respuesta no esperada de RENAP: EL CUI INGRESADO NO CORRESPONDE A UNA PERSONA MAYOR DE EDAD' ? 7 : 1,
            ]);
        }
    }

    public function  bitacora(beneficiarios $beneficiario) {
        try {

            $query = "
                SELECT
                    M.ID AS ASIGNACION_ID,
                    P.NOMBRE AS PROGRAMA,
                    M.NOMBRE AS MODULO_CURSO,
                    BM.CREATED_AT AS FECHA_INSCRIPCION,
                    BM.ANIO_INSCRIPCION,
                    BM.ESTADO,
                    BI_TOP.IDENTIFICADOR,
                    CASE WHEN BM.ESTADO = 'I' THEN BI_TOP.DESCRIPCION ELSE NULL END DESCRIPCION,
                    CASE WHEN BM.ESTADO = 'I' THEN BI_TOP.CREATED_AT ELSE NULL END ULTIMA_MODIFICACION,
                    'MODULO' AS TIPO
                FROM ADM_GDS.BENEFICIARIOS_MODULOS BM
                INNER JOIN ADM_GDS.BENEFICIARIOS B
                    ON BM.BENEFICIARIO_ID = B.ID
                INNER JOIN ADM_GDS.MODULOS M
                    ON BM.MODULO_ID = M.ID
                INNER JOIN ADM_GDS.PROGRAMAS P
                    ON M.PROGRAMA_ID = P.ID
                OUTER APPLY (
                    SELECT *
                    FROM (
                        SELECT BI.IDENTIFICADOR, BI.DESCRIPCION, BI.CREATED_AT
                        FROM ADM_GDS.BITACORA BI
                        WHERE BI.IDENTIFICADOR = BM.ID 
                        AND BI.TABLA = 'BENEFICIARIOS_MODULOS'
                        AND BI.ACCION = 'DESHABILITAR INSCRIPCION MODULO'
                        ORDER BY BI.ID DESC
                    )
                    WHERE ROWNUM = 1
                ) BI_TOP
                WHERE BM.BENEFICIARIO_ID = ?

                UNION ALL
                        
                SELECT
                    DC.ID ASIGNACION_ID,
                    P.NOMBRE PROGRAMA,
                    C.NOMBRE MODULO_CURSO,
                    BC.CREATED_AT FECHA_INSCRIPCION,
                    BC.ANIO_INSCRIPCION,
                    BC.ESTADO,
                    BI_TOP.IDENTIFICADOR,
                    CASE WHEN BC.ESTADO = 'I' THEN BI_TOP.DESCRIPCION ELSE NULL END DESCRIPCION,
                    CASE WHEN BC.ESTADO = 'I' THEN BI_TOP.CREATED_AT ELSE NULL END ULTIMA_MODIFICACION,
                    'CURSO' TIPO
                FROM ADM_GDS.BENEFICIARIOS_CURSOS BC
                INNER JOIN ADM_GDS.BENEFICIARIOS B
                    ON BC.BENEFICIARIO_ID = B.ID
                INNER JOIN ADM_GDS.DETALLES_CURSOS DC
                    ON BC.DETALLE_CURSO_ID = DC.ID
                INNER JOIN ADM_GDS.CURSOS C
                    ON DC.CURSO_ID = C.ID
                INNER JOIN ADM_GDS.PROGRAMAS P
                    ON DC.PROGRAMA_ID = P.ID
                OUTER APPLY (
                    SELECT *
                    FROM (
                        SELECT BI.IDENTIFICADOR, BI.DESCRIPCION, BI.CREATED_AT
                        FROM ADM_GDS.BITACORA BI
                        WHERE BI.IDENTIFICADOR = BC.ID 
                        AND BI.TABLA = 'BENEFICIARIOS_CURSOS'
                        AND BI.ACCION = 'DESHABILITAR INSCRIPCION CURSO'
                        ORDER BY BI.ID DESC -- O BI.CREATED_AT DESC
                    )
                    WHERE ROWNUM = 1
                ) BI_TOP
                WHERE BC.BENEFICIARIO_ID = ?

                UNION ALL

                SELECT
                    DA.ID ASIGNACION_ID,
                    P.NOMBRE PROGRAMA,
                    A.NOMBRE MODULO_CURSO,
                    BA.CREATED_AT FECHA_INSCRIPCION,
                    BA.ANIO_INSCRIPCION,
                    BA.ESTADO,
                    BI_TOP.IDENTIFICADOR,
                    CASE WHEN BA.ESTADO = 'I' THEN BI_TOP.DESCRIPCION ELSE NULL END DESCRIPCION,
                    CASE WHEN BA.ESTADO = 'I' THEN BI_TOP.CREATED_AT ELSE NULL END ULTIMA_MODIFICACION,
                    TA.NOMBRE TIPO
                FROM ADM_GDS.BENEFICIARIOS_ACTIVIDADES BA
                INNER JOIN ADM_GDS.BENEFICIARIOS B
                    ON BA.BENEFICIARIO_ID = B.ID
                INNER JOIN ADM_GDS.DETALLES_ACTIVIDADES DA
                    ON BA.DETALLE_ACTIVIDAD_ID = DA.ID
                INNER JOIN ADM_GDS.TIPOS_ACTIVIDADES TA
                    ON DA.TIPO_ACTIVIDAD_ID = TA.ID
                INNER JOIN ADM_GDS.ACTIVIDADES A
                    ON DA.ACTIVIDAD_ID = A.ID
                INNER JOIN ADM_GDS.PROGRAMAS P
                    ON DA.PROGRAMA_ID = P.ID
                OUTER APPLY (
                    SELECT *
                    FROM (
                        SELECT BI.IDENTIFICADOR, BI.DESCRIPCION, BI.CREATED_AT
                        FROM ADM_GDS.BITACORA BI
                        WHERE BI.IDENTIFICADOR = BA.ID 
                        AND BI.TABLA = 'BENEFICIARIOS_ACTIVIDADES'
                        AND BI.ACCION = 'DESHABILITAR INSCRIPCION ACTIVIDAD'
                        ORDER BY BI.ID DESC -- O BI.CREATED_AT DESC
                    )
                    WHERE ROWNUM = 1
                ) BI_TOP
                WHERE BA.BENEFICIARIO_ID = ?
            ";

            $inscripciones = DB::connection('gds')->select($query,[$beneficiario->id,$beneficiario->id,$beneficiario->id]);

            return response([
                'inscripciones' => $inscripciones,
                'observaciones' => $beneficiario->observaciones->load('usuario'),
                'acciones' => $beneficiario->acciones->load('usuario'),
            ]);      
        } catch (\Throwable $th) {
            return response($th->getMessage());      
        }
    }

    public function  changeStatus(Request $request, beneficiarios $beneficiario) {
        $request->validate([
            'estado' => 'required'
        ]);

        try {

            $beneficiario->estado = $request->estado;
            $beneficiario->save();

            if($beneficiario) {
                bitacora::create([
                    'accion' => bitacora::$acciones[3],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE CAMBIO DE ESTADO AL BENEFICIARIO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $beneficiario->id,
                ]);
            }
            
            return response('Se modifico el estado correctamente');      

        } catch (\Throwable $th) {
            return response($th->getMessage());      
        }
    }

    public function  consultaBeneficiarioUnico(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui ],
        ]);

        try {

            $beneficiarioUnico = beneficiarios::with([
                'domicilio',
                'datos_medicos',
                'datos_academicos',
                'responsable',
                'emergencia'
            ])->where('cui',$request->cui)
            ->first();

            if(!$beneficiarioUnico){

                $beneficiarioUnico = TbBeneficiarioUnico::where('cui',$request->cui)->first();

                if($beneficiarioUnico) {
                    return response([
                        'message' => 'Se encontro información en la base de datos antigua.',
                        'success' => true,
                        'data' => BeneficiarioUnicoResource::make($beneficiarioUnico),
                        'code' => 4,
                    ]);
                }

                $beneficiarioUnico = $this->verifyRenapCui($request->cui);

                if($beneficiarioUnico) {
                    return response([
                        'message' => 'Se consulto en RENAP.',
                        'success' => true,
                        'data' => RenapConsultaResource::make($beneficiarioUnico['data']),
                        'code' => 5
                    ]);
                } else {
                    return response([
                        'message' => 'No se encontro información del cui.',
                        'success' => false,
                        'data' => [],
                        'code' => 6,
                    ],422);
                }

            }

            return response([
                'message' => 'El cui ya existe en la base de datos.',
                'success' => true,
                'data' => $beneficiarioUnico,
                'code' => 2,
            ]);

        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
                'data' => [],
                'code' => $th->getMessage() === 'Respuesta no esperada de RENAP: EL CUI INGRESADO NO CORRESPONDE A UNA PERSONA MAYOR DE EDAD' ? 7 : 1,
            ]);
        }
    }

    public function create(Request $request) {

        DB::connection('gds')->beginTransaction();
        
        try {
            
            $beneficiario = $this->storeBeneficiario($request);

            if($beneficiario) {

                $this->storeDomicilio($request,$beneficiario->id);

                if (
                    isset($request->datos_medicos['tipo_sangre_id']) ||
                    isset($request->datos_medicos['enfermedades_alergias'])
                ){

                    $this->storeDatosMedicos($request,$beneficiario->id);
                }

                if (
                    isset($request->datos_academicos['tipo'])
                ){

                    $this->storeDatosAcademicos($request,$beneficiario->id);
                }

                if($request->edad < 18 ) {
                    // if (
                    //     isset($request->responsable['nombre'])
                    // ){
    
                        $this->storeResponsable($request,$beneficiario->id);
                    // }
                }

                if (
                    isset($request->emergencia['nombres']) &&
                    isset($request->emergencia['apellidos']) &&
                    isset($request->emergencia['celular'])
                ){

                    $this->storeEmergencia($request, $beneficiario->id);
                }
                
                bitacora::create([
                    'accion' => bitacora::$acciones[1],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE CREO BENEFICIARIO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $beneficiario->id,
                ]);
            }



            if(!empty($this->bagValidations)){
                DB::connection('gds')->rollBack();
                return response([
                    'message' => 'Hay campos que no cumplen con las validaciones',
                    'errors' => $this->bagValidations
                ],422);
            }

            DB::connection('gds')->commit();
            
            return response($beneficiario->load([
                'domicilio',
                'datos_medicos',
                'datos_academicos',
                'responsable',
                'emergencia'
            ]));

        } catch (\Throwable $th) {

            DB::connection('gds')->rollBack();
            return response($th->getMessage());

        }
    }

    public function createBeneficiarioActividad(Request $request) {

        DB::connection('gds')->beginTransaction();
        
        try {
            
            $beneficiario = $this->storeBeneficiario($request);

            if($beneficiario) {

                $this->storeDomicilio($request,$beneficiario->id);
                
                bitacora::create([
                    'accion' => bitacora::$acciones[1],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE CREO BENEFICIARIO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $beneficiario->id,
                ]);
            }



            if(!empty($this->bagValidations)){
                DB::connection('gds')->rollBack();
                return response([
                    'message' => 'Hay campos que no cumplen con las validaciones',
                    'errors' => $this->bagValidations
                ],422);
            }

            DB::connection('gds')->commit();
            
            return response($beneficiario->load([
                'domicilio',
            ]));

        } catch (\Throwable $th) {

            DB::connection('gds')->rollBack();
            return response($th->getMessage());

        }
    }

    public function historial(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui ],
        ]);

        try {
            
            $cui = $request->input('cui');
            
            $query = "
                SELECT
                    adminsiaf.tb_alumno_g.cui,
                    adminsiaf.tb_asignacion_g.asignacion,
                    adminsiaf.tb_asignacion_g.anio,
                    adminsiaf.tb_alumno_g.alumno,
                    adminsiaf.tb_asigna_curso_g.tipo_escuela,
                    adminsiaf.tb_beneficiario_unico.primer_nombre ||' '||adminsiaf.tb_beneficiario_unico.primer_apellido AS beneficiario,
                    adminsiaf.tb_asigna_curso_g.grado,
                    adminsiaf.tb_escuela_g.nombre AS programa,
                    adminsiaf.tb_curso_g.nombre AS nombre_curso,
                    adminsiaf.tb_asignacion_g.estatus,
                    adminsiaf.tb_tipo_escuela_g.descripcion AS dependencia,
                    EXTRACT(YEAR FROM adminsiaf.tb_alumno_g.fecha_grabacion) AS fecha_grabacion
                FROM
                    adminsiaf.tb_alumno_g
                    INNER JOIN adminsiaf.tb_asignacion_g 
                        ON adminsiaf.tb_asignacion_g.alumno = adminsiaf.tb_alumno_g.alumno
                        AND adminsiaf.tb_asignacion_g.tipo_escuela = adminsiaf.tb_alumno_g.tipo_escuela
                    INNER JOIN adminsiaf.tb_asigna_curso_g 
                        ON adminsiaf.tb_asignacion_g.asignacion = adminsiaf.tb_asigna_curso_g.asignacion
                    INNER JOIN adminsiaf.tb_beneficiario_unico 
                        ON adminsiaf.tb_alumno_g.cui = adminsiaf.tb_beneficiario_unico.cui
                    INNER JOIN adminsiaf.tb_escuela_g 
                        ON adminsiaf.tb_escuela_g.empresa = adminsiaf.tb_asigna_curso_g.empresa
                        AND adminsiaf.tb_escuela_g.jardin = adminsiaf.tb_asigna_curso_g.jardin
                        AND adminsiaf.tb_escuela_g.tipo_escuela = adminsiaf.tb_asigna_curso_g.tipo_escuela
                    INNER JOIN adminsiaf.tb_curso_g 
                        ON adminsiaf.tb_curso_g.grado = adminsiaf.tb_asigna_curso_g.grado
                        AND adminsiaf.tb_curso_g.tipo_escuela = adminsiaf.tb_asigna_curso_g.tipo_escuela
                    INNER JOIN adminsiaf.tb_tipo_escuela_g 
                        ON adminsiaf.tb_escuela_g.tipo_escuela = adminsiaf.tb_tipo_escuela_g.tipo_escuela
                WHERE adminsiaf.tb_alumno_g.cui = ?
                ORDER BY adminsiaf.tb_asignacion_g.anio DESC
            ";

            $historial = DB::connection('oracle_back_up')->select($query,[$cui]);
            if(!$historial){
                return response('No hay información',422);
            }
        
            return response($historial);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
        
    }

}
