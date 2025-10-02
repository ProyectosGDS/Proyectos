<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\bitacora;
use App\Services\Sap\SapRfc as SAP;
use Illuminate\Http\Request;

class InscripcionesCursosController extends Controller
{
    
    public function update (Request $request, beneficiarios_cursos $inscripcion) {

        $request->validate([
            'beneficiario_id' => 'required',
            'detalle_curso_id' => 'required',
            'estado' => 'nullable'
        ]);

        try {

            $inscripcion->beneficiario_id = $request->beneficiario_id;
            $inscripcion->detalle_curso_id = $request->detalle_curso_id;
            $inscripcion->estado = $request->estado;
            $inscripcion->save();
            
            bitacora::create([
                'accion' => $request->estado == 'A' ? bitacora::$acciones[5] : bitacora::$acciones[6] ,
                'tabla' => 'BENEFICIARIOS_CURSOS',
                'descripcion' => 'SE CAMBIO DE ESTADO INSCRIPCION ID : '.$inscripcion->id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
            ]);

            return response('Inscripción actualizada correctamente');

        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function destroy (beneficiarios_cursos $inscripcion) {

        try {
            
            bitacora::create([
                'accion' => bitacora::$acciones[7],
                'tabla' => 'BENEFICIARIOS_CURSOS',
                'descripcion' => 'SE ELIMINO REGISTRO INSCRIPCION ID : '.$inscripcion->id .' DETALLE CURSO ID :'.$inscripcion->detalle_curso_id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id,
                'beneficiario_id' => $inscripcion->beneficiario_id,
            ]);

            $inscripcion->delete();
            
            

            return response('Inscripción eliminada correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function store_beneficiarios(Request $request) {
        $request->validate([
            'year' => 'required|numeric|digits:4',
            'beneficiarios' => 'required|array',
            'beneficiarios.*.beneficiario_id' => 'required|integer|exists:beneficiarios,id',
            'beneficiarios.*.detalle_curso_id' => 'required|integer|exists:detalles_cursos,id',
            'beneficiarios.*.paga' => 'required_without:beneficiarios.*.id|in:S,N',
            'beneficiarios.*.edad' => 'required_without:beneficiarios.*.id|integer',
            'beneficiarios.*.cui' => 'required_without:beneficiarios.*.id|string|max:13',
            'beneficiarios.*.dependencia' => 'required_without:beneficiarios.*.id|integer',
            'beneficiarios.*.tarifas.tarifa_menor' => 'required_if:paga,S',
            'beneficiarios.*.tarifas.tarifa_mayor' => 'required_if:paga,S',
            'beneficiarios.*.tarifas.no_cuotas' => 'required_if:paga,S|integer',
            'beneficiarios.*.tarifas.mes_inicial' => 'required_if:paga,S|date|date_format:Y-m',
            'beneficiarios.*.tarifas.mes_final' => 'required_if:paga,S|date|date_format:Y-m|after_or_equal:tarifas.mes_inicial',
        ]);

        try {

            $count_beneficiarios = 0;
            
            foreach ($request->beneficiarios as $beneficiario) {
                if(!isset($beneficiario['id'])) { 
                    if(in_array($beneficiario['dependencia'],[5,8])) {
                        if($beneficiario['paga'] == 'S') {
                            if($beneficiario['edad'] > 18 ) {
    
                                $params = [
                                    'TIPO_ESCUELA' => $beneficiario['depenencia'] == '8' ? '1' : '2', 
                                    'ALUMNO' => '',
                                    'DPI' => $beneficiario['cui'],
                                    'PRIMER_NOMBRE' => $beneficiario['beneficiario']['primer_nombre'],
                                    'SEGUNDO_NOMBRE' => $beneficiario['beneficiario']['segundo_nombre'],
                                    'PRIMER_APELLIDO' => $beneficiario['beneficiario']['primer_apellido'],
                                    'SEGUNDO_APELLIDO' => $beneficiario['beneficiario']['segundo_apellido'],
                                    'FECHA_NACIMIENTO' => date('Ymd',strtotime($beneficiario['beneficiario']['fecha_nacimiento'])),
                                    'DIRECCION' => $beneficiario['beneficiario']['domicilio']['direccion'],
                                    'ZONA' => $beneficiario['beneficiario']['domicilio']['zona_id'],
                                ];
                            } else {
                                $params = [
                                    'TIPO_ESCUELA' => $beneficiario['depenencia'] == '8' ? '1' : '2', 
                                    'ALUMNO' => '',
                                    'DPI' => $beneficiario['cui'],
                                    'PRIMER_NOMBRE' => $beneficiario['beneficiario']['primer_nombre'],
                                    'SEGUNDO_NOMBRE' => $beneficiario['beneficiario']['segundo_nombre'],
                                    'PRIMER_APELLIDO' => $beneficiario['beneficiario']['primer_apellido'],
                                    'SEGUNDO_APELLIDO' => $beneficiario['beneficiario']['segundo_apellido'],
                                    'FECHA_NACIMIENTO' => date('Ymd',strtotime($beneficiario['beneficiario']['fecha_nacimiento'])),
                                    'DIRECCION' => $beneficiario['beneficiario']['domicilio']['direccion'],
                                    'ZONA' => $beneficiario['beneficiario']['domicilio']['zona_id'],
                                    'PNOMBRE_ENCARGADO' => '',
                                    'SNOMBRE_ENCARGADO' => '',
                                    'PAPELLIDO_ENCARGADO' => '',
                                    'SAPELLIDO_ENCARGADO' => '',
                                    'FNACIMIENTO_ENCARGADO' => date('Ymd',strtotime('')),
                                    'DPI_ENCARGADO' => $beneficiario['responsable']['cui']
                                ];
                            }
                        }
                        // SAP::rfc_name('Z_ZFUN_PSCD_00003_004')->params($params);
                    } 

                    $inscripcion_curso = beneficiarios_cursos::create([
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                        'detalle_curso_id' => $beneficiario['detalle_curso_id'],
                        'anio_inscripcion' => $request->year,
                        'estado' => 'A',
                        'created_at' => now(),
                    ]);

                    bitacora::create([
                        'accion' => bitacora::$acciones[4],
                        'tabla' => 'BENEFICIARIOS_CURSOS',
                        'descripcion' => 'SE INSCRIBIO BENEFICIARIO ID : '.$inscripcion_curso->id,
                        'created_at' => now(),
                        'usuario_id' => auth()->user()->id,
                        'beneficiario_id' => $beneficiario['beneficiario_id'],
                    ]);

                    $count_beneficiarios ++;
                }
            }
    
            return response($count_beneficiarios.' Beneficiarios nuevos asignados correctamente');
            
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function get_beneficiarios (int $detalle_curso_id, string $year) {

        try {

            $beneficiarios_inscritos = beneficiarios_cursos::with([
                    'beneficiario',
                    'curso.horarios',
                    'curso.programa',
                    'curso.curso',
                    'curso.sede',
                    'curso.tarifas'
                ])->where('detalle_curso_id',$detalle_curso_id)
                ->latest('id')
                ->where('anio_inscripcion',$year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

}
