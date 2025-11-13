<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios;
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
            // 'beneficiarios.*.tarifas.mes_final' => 'required_if:paga,S|date|date_format:Y-m|after_or_equal:tarifas.mes_inicial',
            'beneficiarios.*.objeto_contrato' => 'required_if:beneficiarios.*.paga,S',
            'beneficiarios.*.op_principal' => 'required_if:beneficiarios.*.paga,S',
            'beneficiarios.*.op_parcial' => 'required_if:beneficiarios.*.paga,S',
        ]);

        try {

            $count_beneficiarios = 0;
            
            foreach ($request->beneficiarios as $row) {
                if(!isset($row['id'])) { 
                    if(in_array($row['dependencia'],['5','8']) && $row['paga'] ==='S') {
                            
                        $params = [
                            'TIPO_ESCUELA' => $row['dependencia'] == '8' ? '1' : '2', 
                            'ALUMNO' => strval($row['beneficiario']['codigo_alumno']),
                            'DPI' => $row['cui'],
                            'PRIMER_NOMBRE' => $row['beneficiario']['primer_nombre'],
                            'SEGUNDO_NOMBRE' => $row['beneficiario']['segundo_nombre'] ?? '.',
                            'PRIMER_APELLIDO' => $row['beneficiario']['primer_apellido'],
                            'SEGUNDO_APELLIDO' => $row['beneficiario']['segundo_apellido'] ?? '.',
                            'FECHA_NACIMIENTO' => date('Ymd',strtotime($row['beneficiario']['fecha_nacimiento'])),
                            'DIRECCION' => $row['beneficiario']['domicilio']['direccion'] ?? null,
                            'ZONA' => 'ZONA '.$row['beneficiario']['domicilio']['zona_id'] ?? null,
                        ];

                        if($row['edad'] < 18 ) {

                            $params += [
                                'PNOMBRE_ENCARGADO' => $row['responsable']['primer_nombre'],
                                'SNOMBRE_ENCARGADO' => $row['responsable']['segundo_nombre'] ?? '.',
                                'PAPELLIDO_ENCARGADO' => $row['responsable']['primer_apellido'],
                                'SAPELLIDO_ENCARGADO' => $row['responsable']['segundo_apellido'] ?? '.',
                                'FNACIMIENTO_ENCARGADO' => date('Ymd',strtotime($row['responsable']['fecha_nacimiento'])),
                                'DPI_ENCARGADO' => $row['responsable']['cui']
                            ];
                        }

                        
                        $interlocutor = SAP::rfc_name('Z_ZFUN_PSCD_00003_004')->params($params);

    
                        if(!empty(trim($interlocutor['IC_ENCARGADO']))) {
                            $ic = trim($interlocutor['IC_ENCARGADO']);
                        } else {
                            $ic = trim($interlocutor['IC_ALUMNO']);
                        }

                        if($ic) {

                            $persona = beneficiarios::where('cui',$row['beneficiario']['cui'])->update([
                                'interlocutor' => $ic
                            ]);

                            if($persona){
                                if($row['tarifas']['inscripcion']) {
                                    $params = [
                                        'INTERLOCUTOR' => $ic, 
                                        'OP_PRINCIPAL' => $row['sede_op_principal'] ?? $row['op_principal'],
                                        'OP_PARCIAL' => $row['sede_op_parcial'] ?? $row['op_parcial'],
                                        'OBJETO_CONTRATO' => $row['sede_oc'] ?? $row['objeto_contrato'],
                                        'VALOR' => strval(floatval($row['tarifas']['inscripcion'])),
                                        'PERIODO' => date('my',strtotime($this->sumMonth($row['tarifas']['mes_inicial'],0))),
                                        'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($row['tarifas']['mes_inicial'],0)),
                                        'DESCRIPCION' => 'INSCRIPCION '.mb_strtoupper($row['nombre_curso']),
                                        'LLAVE_RECONCILIACION' => date('ymd')
                                    ];
                                    SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);

                                }

                                // for ($i=0; $i <= intval($row['tarifas']['no_cuotas']) - 1 ; $i++) { 
                                
                                    // $params = [
                                    //     'INTERLOCUTOR' => $ic, 
                                    //     'OP_PRINCIPAL' => $row['sede_op_principal'] ?? $row['op_principal'],
                                    //     'OP_PARCIAL' => $row['sede_op_parcial'] ?? $row['op_parcial'],
                                    //     'OBJETO_CONTRATO' => $row['sede_oc'] ?? $row['objeto_contrato'],
                                    //     'VALOR' => $row['edad'] > 18 ? strval(floatval($row['tarifas']['tarifa_mayor'])) : strval(floatval($row['tarifas']['tarifa_mayor'])),
                                    //     'PERIODO' => date('my',strtotime($this->sumMonth($row['tarifas']['mes_inicial'],$i))),
                                    //     'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($row['tarifas']['mes_inicial'],$i)),
                                    //     'DESCRIPCION' => 'PAGO CUOTA '. mb_strtoupper($row['nombre_curso']),
                                    //     'LLAVE_RECONCILIACION' => 'OCT2025'
                                    // ];

                                    $params = [
                                        'INTERLOCUTOR' => $ic, 
                                        'OP_PRINCIPAL' => $row['sede_op_principal'] ?? $row['op_principal'],
                                        'OP_PARCIAL' => $row['sede_op_parcial'] ?? $row['op_parcial'],
                                        'OBJETO_CONTRATO' => $row['sede_oc'] ?? $row['objeto_contrato'],
                                        'VALOR' => $row['edad'] > 18 ? strval(floatval($row['tarifas']['tarifa_mayor'])) : strval(floatval($row['tarifas']['tarifa_menor'])),
                                        'PERIODO' => date('my',strtotime($this->sumMonth($row['tarifas']['mes_inicial'],0))),
                                        'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($row['tarifas']['mes_inicial'],0)),
                                        'DESCRIPCION' => 'PAGO CUOTA '. mb_strtoupper($row['nombre_curso']),
                                        'LLAVE_RECONCILIACION' => date('ymd')
                                    ];

                                    SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
                                // } 
                            }
                        }   
                    } 

                    $inscripcion_curso = beneficiarios_cursos::create([
                        'beneficiario_id' => $row['beneficiario_id'],
                        'detalle_curso_id' => $row['detalle_curso_id'],
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
                        'beneficiario_id' => $row['beneficiario_id'],
                    ]);

                    $count_beneficiarios ++;
                }
            }
    
            return response($count_beneficiarios.' beneficiarios fueron inscritos con exito.');
            
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

    public function sumMonth(string $fecha, string $mesesASumar) {
        $timestamp = strtotime($fecha . "-01");
        $mesesASumar = strval($mesesASumar);
        $nuevaFecha = date("Y-m", strtotime("+$mesesASumar month", $timestamp));
        return $nuevaFecha;
        
    }

    public function ultimoDiaFormatoYmd(string $fechaYm): string {
        $timestamp = strtotime($fechaYm . "-01");
        $ultimoDia = date("t", $timestamp);
        return date("Ym", $timestamp) . $ultimoDia;
    }

    public function asignar_beca(beneficiarios_cursos $inscripcion) {
        try {
            $inscripcion->becado = 1;
            $inscripcion->save();

            return response([
                'message' => 'Beca asignada correctamente',
            ]);

        } catch (\Throwable $th) {
            return response([
                'error' => 'Error en asignar beca',
                'message' => $th->getMessage()
            ]);
        }
    }

}
