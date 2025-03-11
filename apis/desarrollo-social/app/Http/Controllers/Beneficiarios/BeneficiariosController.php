<?php

namespace App\Http\Controllers\Beneficiarios;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiarioUnicoResource;
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
        
        $search = $request->input('search') ?? '';
        $column = $request->input('column') ?? 'id';
        $order = $request->input('order') ?? 'desc';
        $per_page = $request->input('per_page') ?? 10;

        try {

            $beneficiarios = beneficiarios::where(function ($query) use ($search) {
                $query->where('cui','LIKE','%'.$search.'%')
                    ->orWhereRaw(
                        "LOWER(CONCAT(PRIMER_NOMBRE,' ',SEGUNDO_NOMBRE,' ',PRIMER_APELLIDO,' ',SEGUNDO_APELLIDO)) LIKE ?",
                        ["%" . strtolower($search) . "%"]
                    )
                    ->orWhere('celular','LIKE','%'. $search .'%')
                    ->orWhere('correo','LIKE','%'. $search .'%');
            })
            ->orderBy(($column === 'nombre_completo') ? 'primer_nombre' : $column, $order)
            ->paginate($per_page);

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
                        isset($request->responsable['nombre'])
                    ){
    
                        $this->storeResponsable($request,$beneficiario->id);
                    }
                }

                if (
                    isset($request->emergencia['nombre'])
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

            if (!is_null($beneficiario->domicilios)) {
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
                        isset($request->responsable['nombre'])
                    ) {
                        $this->storeResponsables($request, $beneficiario->id);
                    }
                }
            }

            if(!is_null($beneficiario->emergencia)) {
                $this->updateEmergencia($request, $beneficiario);
            } else {
                if (
                    isset($request->emergencia['nombre'])
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

    public function consultaBackUp(Request $request) {
        $request->validate([
            'cui' => ['required','numeric','digits:13',new ValidateCui ],
        ]);

        try {
            
            $beneficiarioUnico = TbBeneficiarioUnico::where('cui',$request->cui)->firstOrFail();
        
            return response(BeneficiarioUnicoResource::make($beneficiarioUnico));

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function  bitacora(beneficiarios $beneficiario) {
        try {

            $query = "
                SELECT
                    bm.id inscripcion_id,
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
                WHERE bm.beneficiario_id = ?
                
                UNION ALL
                    
                SELECT
                    bc.id inscripcion_id,
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
                WHERE bc.beneficiario_id = ?
            ";

            $inscripciones = DB::connection('gds')->select($query,[$beneficiario->id,$beneficiario->id]);

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
                    return response(BeneficiarioUnicoResource::make($beneficiarioUnico));
                }

                return response('No hay información',422);
            }

            return response($beneficiarioUnico);

        } catch (\Throwable $th) {
            return response($th->getMessage());
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
                    if (
                        isset($request->responsable['nombre'])
                    ){
    
                        $this->storeResponsable($request,$beneficiario->id);
                    }
                }

                if (
                    isset($request->emergencia['nombre'])
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

}
