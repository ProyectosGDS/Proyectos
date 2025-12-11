<?php

namespace App\Http\Controllers\Beneficiarios;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiarioUnicoResource;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\bitacora;
use App\Models\Muni\TbBeneficiarioUnico;
use App\Traits\TraitBeneficiarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtranjerosController extends Controller
{
    use TraitBeneficiarios;

    public function store(Request $request) {

        DB::connection('gds')->beginTransaction();

        try {

            $extranjero = $this->storeExtranjero($request);

            if ($extranjero) {

                $this->storeDomicilio($request,$extranjero->id);

                if (
                    isset($request->datos_medicos['tipo_sangre_id']) ||
                    isset($request->datos_medicos['enfermedades_alergias'])
                ){

                    $this->storeDatosMedicos($request,$extranjero->id);
                }

                if ( isset($request->datos_academicos['tipo']) ){

                    $this->storeDatosAcademicos($request,$extranjero->id);
                }

                if($request->edad < 18 ) {
                    if (
                        isset($request->responsable['nombres']) &&
                        isset($request->responsable['apellidos']) &&
                        isset($request->responsable['fecha_nacimiento']) &&
                        isset($request->responsable['cui'])
                    ){
    
                        $this->storeResponsableExtranjero($request,$extranjero->id);
                    }
                }

                if (
                    isset($request->emergencia['nombres']) &&
                    isset($request->emergencia['apellidos']) &&
                    isset($request->emergencia['celular'])
                ){

                    $this->storeEmergencia($request, $extranjero->id);
                }
                
                bitacora::create([
                    'accion' => bitacora::$acciones[1],
                    'tabla' => 'BENEFICIARIOS',
                    'descripcion' => 'SE CREO BENEFICIARIO EXTRANJERO',
                    'created_at' => now(),
                    'usuario_id' => auth()->user()->id,
                    'beneficiario_id' => $extranjero->id,
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
            
            return response([
                'message' => 'Se ha almacenado los datos correctamente',
                'data' => $extranjero
            ]);
            
        } catch (\Throwable $th) {
            DB::connection('gds')->rollBack();
            return response([
                'error' => 'Error en la creacion del beneficiario extranjero',
                'message' => $th->getMessage()
            ]);
        }
    }
    
    public function searchExtranjero(Request $request) {
        $request->validate([
            'pasaporte' => 'required'
        ]);

        try {
    
            $extranjero = beneficiarios::where('pasaporte',$request->pasaporte)
                ->orWhere('cui',$this->formatoCui($request->pasaporte))
                ->first();

            if(!$extranjero) {

                $extranjero = TbBeneficiarioUnico::where('pasaporte_alumno',$request->pasaporte)
                    ->orWhere('cui',$this->formatoCui($request->pasaporte))->first();

                if($extranjero) {
                    return response([
                        'message' => 'Se encontro información en la base de datos antigua',
                        'data' => BeneficiarioUnicoResource::make($extranjero),
                        'code' => 2
                    ]);
                }

                return response([
                    'message' => 'No se encontro información',
                    'data' => [],
                    'code' => 3
                ]);
            }

            return response([
                'message' => 'Se encontro información en la base de datos actual',
                'data' => $extranjero,
                'code' => 1
            ]);
            
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'data' => [],
                'code' => 'error',
            ],500);
        }
    }

    public function formatoCui($cadena) {
        $numeros = preg_replace('/\D+/', '', $cadena);
        return str_pad($numeros, 13, '0', STR_PAD_RIGHT);
    }
}
