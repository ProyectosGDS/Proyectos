<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\bitacora;
use App\Traits\TraitBeneficiarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    use TraitBeneficiarios;

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
                    'accion' => bitacora::$acciones[16],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'INSCRIPCION EN LINEA PARTICIPACION CIUDADANA',
                    'created_at' => now(),
                    'usuario_id' => null,
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
}
