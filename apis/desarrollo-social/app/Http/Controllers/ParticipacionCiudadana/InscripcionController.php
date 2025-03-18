<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\bitacora;
use App\Traits\TraitBeneficiarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    use TraitBeneficiarios;

    public function store(Request $request) {

        try {

            if(isset($request->id)) {

                return $this->inscripcion_store($request->formacion_tipo, $request->formacion_id, $request->id);

            } else {
                
                DB::connection('gds')->beginTransaction();
          
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
                    
                    // bitacora::create([
                    //     'accion' => bitacora::$acciones[16],
                    //     'tabla' => 'BENEFICIARIOS',
                    //     'descripcion' => 'INSCRIPCION EN LINEA PARTICIPACION CIUDADANA',
                    //     'created_at' => now(),
                    //     'usuario_id' => null,
                    //     'beneficiario_id' => $beneficiario->id,
                    // ]);
                }

                if(!empty($this->bagValidations)){
                    DB::connection('gds')->rollBack();
                    return response([
                        'message' => 'Hay campos que no cumplen con las validaciones',
                        'errors' => $this->bagValidations
                    ],422);
                }

                DB::connection('gds')->commit();

                return $this->inscripcion_store($request->formacion_tipo, $request->formacion_id, $beneficiario->id);
                
            }

        } catch (\Throwable $th) {
            DB::connection('gds')->rollBack();
            return response($th->getMessage());
        }
    }

    public function inscripcion_store(string $formacion_tipo, int $formacion_id, int $beneficiario_id) {

        if($formacion_tipo == 'modulo') {

            $inscripcion = beneficiarios_modulos::where('beneficiario_id',$beneficiario_id)
                ->where('modulo_id',$formacion_id)
                ->whereYear('created_at',date('Y'))
                ->first();

            if($inscripcion) {
                return response('Ya estas inscrito a este módulo');
            }
            
            beneficiarios_modulos::create([
                'beneficiario_id' => $beneficiario_id,
                'modulo_id' => $formacion_id,
                'created_at' => now(),
                'estado' => 'A',
            ]);
            

        } else {

            $inscripcion = beneficiarios_cursos::where('beneficiario_id',$beneficiario_id)
                ->where('detalle_curso_id',$formacion_id)
                ->whereYear('created_at',date('Y'))
                ->first();

            if($inscripcion) {
                return response('Ya estas inscrito a este curso');
            }

            beneficiarios_cursos::create([
                'beneficiario_id' => $beneficiario_id,
                'detalle_curso_id' => $formacion_id,
                'created_at' => now(),
                'estado' => 'A',
            ]);

        }

        return response('Pre-inscripcion realizada con exito');
        
    }

    public function beneficiario_store(Request $request) {

        
    }
}
