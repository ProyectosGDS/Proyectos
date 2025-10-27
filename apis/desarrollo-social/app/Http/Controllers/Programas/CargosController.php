<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\programas;
use App\Services\Sap\SapRfc as SAP;
use Illuminate\Http\Request;

class CargosController extends Controller
{
    public function generar_partidas(Request $request, programas $programa) {

        $request->validate([
            'anio_mes' => 'required|date|date_format:Y-m'
        ]);

        try {

            $mes_pago = date('m',strtotime($request->anio_mes . '-01'));

            $temporalidad_pago = [
                'MENSUAL' => [2,3,4,5,6,7,8,9,10,11],
                'BIMENSUAL' => [2,4,6,8,10],
                'TRIMESTRAL' => [2,5,8],
                'SEMESTRAL' => [2,6],
                'ANUAL' => [2]
            ];

            $modulos = $programa->modulos()->where('paga','S');
            // $cursos = $programa->cursos()->where('paga','S');

            foreach ($modulos as $modulo) {
                if(in_array(intval($mes_pago),$temporalidad_pago[$modulo->tarifas->temporalidad])){ 
                    foreach($modulo->beneficiarios as $beneficiario ) {
                        if(!$beneficiario->becado) {
                            $params = [
                                'INTERLOCUTOR' => $beneficiario->interlocutor, 
                                'OP_PRINCIPAL' => $programa->escuela->op_principal,
                                'OP_PARCIAL' => $programa->escuela->op_parcial,
                                'OBJETO_CONTRATO' => $programa->escuela->objeto_contrato,
                                'VALOR' => $beneficiario->edad > 18 ? strval(floatval($modulo->tarifas->tarifa_mayor)) : strval(floatval($modulo->tarifas->tarifa_menor)),
                                'PERIODO' => date('my',strtotime($this->sumMonth($request->anio_mes,0))),
                                'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($request->anio_mes,0)),
                                'DESCRIPCION' => 'PAGO CUOTA '. mb_strtoupper($modulo->nombre),
                                'LLAVE_RECONCILIACION' => 'GDS'.strval(date('Y-m-d'))
                            ];
        
                            SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
                        }
                    } 
                }
            }

            return response([
                'message' => 'Partidas generadas exitosamente'
            ]);

        } catch (\Throwable $th) {
            return response([
                'error' => 'Error al generar partidas',
                'message' => $th->getMessage()
            ]);
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

}
