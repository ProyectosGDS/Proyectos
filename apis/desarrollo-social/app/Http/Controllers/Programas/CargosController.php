<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\programas;
use App\Services\Sap\SapRfc as SAP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CargosController extends Controller
{
    private const TEMPORALIDAD_PAGO = [
        'MENSUAL' => [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
        'BIMENSUAL' => [2, 4, 6, 8, 10],
        'TRIMESTRAL' => [2, 5, 8],
        'SEMESTRAL' => [2, 6],
        'ANUAL' => [2]
    ];

    public function generar_partidas(Request $request, programas $programa) {

        try {
            $validated = $request->validate([
                'anio_mes' => 'required|date|date_format:Y-m'
            ]);

            $mesPago = (int) date('m', strtotime($validated['anio_mes'] . '-01'));
            $anioPago = (int) date('Y', strtotime($validated['anio_mes'] . '-01'));
            
            $modulos = $programa->modulos()
                ->where('paga', 'S')
                ->with(['tarifas', 'beneficiarios'])
                ->get();

                
            $cursos = $programa->cursos()
                ->where('paga', 'S')
                ->with(['tarifas', 'beneficiarios','curso'])
                ->get();

            $partidasGeneradas = $this->procesarModulos($modulos, $mesPago,$anioPago, $validated['anio_mes'], $programa);
            $partidasGeneradas += $this->procesarCursos($cursos, $mesPago,$anioPago, $validated['anio_mes'], $programa);

            return response()->json([
                'message' => 'Partidas generadas exitosamente',
                'partidas_generadas' => $partidasGeneradas
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'message' => $e->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('Error al generar partidas', [
                'programa_id' => $programa->id ?? 'N/A',
                'anio_mes' => $request->anio_mes ?? 'N/A',
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al generar partidas',
                'message' => 'Ocurrió un error interno. Por favor, contacte al administrador.'
            ], 500);
        }
    }

    private function procesarModulos($modulos, int $mesPago, $anioPago, string $anioMes, programas $programa): int {
        $partidasGeneradas = 0;

        foreach ($modulos as $modulo) {
            if (!$this->debeProcesarModulo($modulo, $mesPago)) {
                continue;
            }

            foreach ($modulo->beneficiarios as $beneficiario) {
                if ($this->esBeneficiarioValido($beneficiario, $anioPago)) {
                    try {
                        $this->generarPartidaSAP($beneficiario, $modulo, $modulo->nombre, $anioMes, $programa);
                        $partidasGeneradas++;
                    } catch (\Throwable $th) {
                        Log::error('Error al generar partida SAP', [
                            'beneficiario_id' => $beneficiario->id,
                            'modulo_id' => $modulo->id,
                            'error' => $th->getMessage()
                        ]);
                        continue;
                    }
                }
            }
        }

        return $partidasGeneradas;
    }

    private function procesarCursos($cursos, int $mesPago, $anioPago, string $anioMes, programas $programa): int {
        $partidasGeneradas = 0;

        foreach ($cursos as $curso) {
            if (!$this->debeProcesarModulo($curso, $mesPago)) {
                continue;
            }

            foreach ($curso->beneficiarios as $beneficiario) {
                if ($this->esBeneficiarioValido($beneficiario, $anioPago)) {
                    try {
                        $this->generarPartidaSAP($beneficiario, $curso, $curso->curso->nombre, $anioMes, $programa);
                        $partidasGeneradas++;
                    } catch (\Throwable $th) {
                        Log::error('Error al generar partida SAP', [
                            'beneficiario_id' => $beneficiario->id,
                            'detalle_curso_id' => $curso->id,
                            'error' => $th->getMessage()
                        ]);
                        continue;
                    }
                }
            }
        }

        return $partidasGeneradas;
    }

    private function debeProcesarModulo($modulo, int $mesPago): bool {
        $temporalidad = $modulo->tarifas->temporalidad ?? null;
        
        if (!$temporalidad || !isset(self::TEMPORALIDAD_PAGO[$temporalidad])) {
            Log::warning('Temporalidad no válida o no encontrada', [
                'modulo_id' => $modulo->id,
                'temporalidad' => $temporalidad
            ]);
            return false;
        }

        return in_array($mesPago, self::TEMPORALIDAD_PAGO[$temporalidad], true);
    }

    private function esBeneficiarioValido($beneficiario,$anioPago) {
        return !$beneficiario->pivot->becado && 
            isset($beneficiario->interlocutor) && 
            $beneficiario->pivot->anio_inscripcion == $anioPago;
    }

    private function generarPartidaSAP($beneficiario, $modulo, string $nombre_modulo_curso, string $anioMes, programas $programa): void {
        $periodo = $this->sumMonth($anioMes, 0);
        $fechaVencimiento = $this->ultimoDiaFormatoYmd($periodo);
        $tarifa = $this->calcularTarifa($beneficiario, $modulo);

        $params = [
            'INTERLOCUTOR' => $beneficiario->interlocutor,
            'OP_PRINCIPAL' => $programa->escuela->op_principal,
            'OP_PARCIAL' => $programa->escuela->op_parcial,
            'OBJETO_CONTRATO' => $programa->escuela->objeto_contrato,
            'VALOR' => strval($tarifa),
            'PERIODO' => date('my', strtotime($periodo . '-01')),
            'FECHA_VENCIMIENTO' => $fechaVencimiento,
            'DESCRIPCION' => 'PAGO CUOTA ' . mb_strtoupper($nombre_modulo_curso),
            'LLAVE_RECONCILIACION' => 'GDS' . date('Y-m-d')
        ];

        $this->validarParametrosSAP($params);
        
        SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
    }

    private function calcularTarifa($beneficiario, $modulo): float {
        $edad = $beneficiario->edad ?? 0;
        
        if ($edad > 18) {
            return floatval($modulo->tarifas->tarifa_mayor ?? 0);
        }
        
        return floatval($modulo->tarifas->tarifa_menor ?? 0);
    }

    private function validarParametrosSAP(array $params): void {
        $camposRequeridos = [
            'INTERLOCUTOR', 'OP_PRINCIPAL', 'OP_PARCIAL', 
            'OBJETO_CONTRATO', 'VALOR', 'PERIODO', 
            'FECHA_VENCIMIENTO', 'DESCRIPCION'
        ];

        foreach ($camposRequeridos as $campo) {
            if (empty($params[$campo])) {
                throw new \InvalidArgumentException("El parámetro SAP requerido '$campo' está vacío");
            }
        }

        if (!is_numeric($params['VALOR']) || floatval($params['VALOR']) <= 0) {
            throw new \InvalidArgumentException("El valor de la tarifa debe ser un número positivo");
        }
    }

    public function sumMonth(string $fecha, int $mesesASumar): string {
        $timestamp = strtotime($fecha . "-01");
        
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Fecha inválida: $fecha");
        }
        
        return date("Y-m", strtotime("+$mesesASumar month", $timestamp));
    }

    public function ultimoDiaFormatoYmd(string $fechaYm): string {
        $timestamp = strtotime($fechaYm . "-01");
        
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Fecha inválida: $fechaYm");
        }
        
        $ultimoDia = date("t", $timestamp);
        return date("Ym", $timestamp) . $ultimoDia;
    }
}