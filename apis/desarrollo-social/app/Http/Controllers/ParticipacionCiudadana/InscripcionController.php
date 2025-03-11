<?php

namespace App\Http\Controllers\ParticipacionCiudadana;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\departamentos;
use App\Models\adm_gds\enfermedades;
use App\Models\adm_gds\escolaridad;
use App\Models\adm_gds\estado_civil;
use App\Models\adm_gds\etnias;
use App\Models\adm_gds\grupo_habitacional;
use App\Models\adm_gds\parentescos;
use App\Models\adm_gds\tipo_sangre;
use App\Models\adm_gds\vias;
use App\Models\adm_gds\zonas;
use App\Traits\TraitPersonas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    use TraitPersonas;

    public function store(Request $request) {

        DB::connection('oracle_gds')->beginTransaction();
        
        try {
            
            $persona = $this->createPersonas($request);

            if($persona) {

                if (
                    $request->has('domicilios.numero_via') ||
                    $request->has('domicilios.id_via') ||
                    $request->has('domicilios.nomenclatura') ||
                    $request->has('domicilios.complemento') ||
                    $request->has('domicilios.zona') ||
                    $request->has('domicilios.id_grupo_zona')
                ){

                    $this->createDomicilios($request,$persona->id_persona);
                }

                if (
                    $request->has('datos_academicos.id_escolaridad') ||
                    $request->has('datos_academicos.tipo_establecimiento') ||
                    $request->has('datos_academicos.establecimiento') ||
                    $request->has('datos_academicos.titulo')
                ) {

                    $this->createDatosAcademicos($request,$persona->id_persona);
                }


                if(
                    $request->has('datos_medicos.id_tipo') ||
                    $request->has('datos_medicos.id_enfermedad') ||
                    $request->has('datos_medicos.medicamentos') ||
                    $request->has('datos_medicos.dosis')
                ){
                    $this->createDatosMedicos($request,$persona->id_persona);
                }


                if(isset($request->edad) && (intval($request->edad) < 18)) {
                    if(
                        $request->has('responsables.id_parentesco') ||
                        $request->has('responsables.cui') ||
                        $request->has('responsables.nombre') ||
                        $request->has('responsables.email') ||
                        $request->has('responsables.celular') ||
                        $request->has('responsables.domicilio') ||
                        $request->has('responsables.zona')
                    ) {
                        $this->createResponsables($request,$persona->id_persona);
                    }
                }



                if(
                    $request->has('emergencia.id_parentesco') ||
                    $request->has('emergencia.cui') ||
                    $request->has('emergencia.nombre') ||
                    $request->has('emergencia.email') ||
                    $request->has('emergencia.celular') ||
                    $request->has('emergencia.domicilio') ||
                    $request->has('emergencia.zona')
                ) {
                    $this->createEmergencia($request,$persona->id_persona);
                }

                
                $persona->cursos_x_persona()->create([
                    'id_clase' => $request->id_clase,
                    'estatus' => 'A',
                    'usuario' => auth()->user()->cui ?? null,
                    'fechau' => now(),
                ]);

            }



            if(!empty($this->bagValidations)){
                DB::connection('oracle_gds')->rollBack();
                return response(['errors' => $this->bagValidations],422);
            }

            DB::connection('oracle_gds')->commit();
            
            return response('Se ha almacenado los datos correctamente');

        } catch (\Throwable $th) {

            DB::connection('oracle_gds')->rollBack();
            return response($th->getMessage(),422);

        }
    }

    public function catalogos() {
        try {

            $catalogos = [
                'departamentos' => departamentos::all(),
                'etnias' => etnias::all(),
                'estado_civil' => estado_civil::all(),
                'vias' => vias::all(),
                'zonas' => zonas::all(),
                'grupo_habitacional' => grupo_habitacional::all(),
                'tipo_sangre' => tipo_sangre::all(),
                'enfermedades' => enfermedades::all(),
                'escolaridades' => escolaridad::all(),
                'parentescos' => parentescos::all(),
            ];
            
            return response($catalogos);

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }
}
