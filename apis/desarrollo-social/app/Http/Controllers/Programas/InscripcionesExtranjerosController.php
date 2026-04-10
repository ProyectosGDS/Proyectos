<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios;
use App\Models\adm_gds\beneficiarios_actividades;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\bitacora;
use App\Models\adm_gds\detalles_actividades;
use App\Models\adm_gds\detalles_cursos;
use App\Models\adm_gds\modulos;
use App\Services\Sap\SapRfc as SAP;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionesExtranjerosController extends Controller
{
    public function inscripcion(Request $request) {
        $request->validate([
            'year' => 'required|digits:4',
            'month' => 'required|digits_between:1,2|between:1,12',
            'tipo' => 'required|string|in:modulo,curso,actividad',
            'codigo' => 'required|integer',
            'extranjero_id' => 'required|integer|exists:beneficiarios,id'
        ]);

        DB::connection('gds')->beginTransaction();

        try {

            $anio_mes = date('Y-m',strtotime($request['year'].'-'.$request['month']));

            $recurso = $this->buscarRecurso($request->tipo, $request->codigo);

            if (!$recurso) {
                throw new Exception('No existe el recurso', 404);
            }

            $yaInscrito = $this->verificarUnicoInscrito((int)$request->year, $request->tipo, (int)$recurso->id, (int)$request->extranjero_id);

            if ($yaInscrito) {
                throw new Exception('El beneficiario ya está inscrito en el recurso', 409);
            }
    
            $dependenciaId = isset($recurso->programa->dependencia_id) ? (int)$recurso->programa->dependencia_id : null;

            if (in_array($dependenciaId, [5, 8])) {
                if (isset($recurso->paga) && $recurso->paga === 'S') {
                    $interlocutor = $this->consultaCreacionInterlocutorSap($recurso, $request->extranjero_id);

                    $partida = $this->crearPartidasSap($interlocutor, $recurso, $request->extranjero_id, $request->tipo, $anio_mes);

                    if (!$partida) {
                        throw new Exception('No se crearon las partidas', 500);
                    }
                }
            }

            $inscripcion = null;
            $bitacora = [
                'accion' => null,
                'tabla' => null,
            ];

            switch ($request->tipo) {
                case 'modulo':
                    $inscripcion = beneficiarios_modulos::create([
                        'beneficiario_id' => $request->extranjero_id,
                        'modulo_id' => $recurso->id,
                        'anio_inscripcion' => $request->year,
                        'estado' => 'A',
                        'created_at' => now(),
                    ]);

                    $bitacora['accion'] = bitacora::$acciones[8] ?? null;
                    $bitacora['tabla'] = 'BENEFICIARIOS_MODULOS';
                    break;

                case 'curso':
                    $inscripcion = beneficiarios_cursos::create([
                        'beneficiario_id' => $request->extranjero_id,
                        'detalle_curso_id' => $recurso->id,
                        'anio_inscripcion' => $request->year,
                        'estado' => 'A',
                        'created_at' => now(),
                    ]);

                    $bitacora['accion'] = bitacora::$acciones[4] ?? null;
                    $bitacora['tabla'] = 'BENEFICIARIOS_CURSOS';
                    break;

                case 'actividad':
                    $inscripcion = beneficiarios_actividades::create([
                        'beneficiario_id' => $request->extranjero_id,
                        'detalle_actividad_id' => $recurso->id,
                        'created_at' => now(),
                        'estado' => 'A',
                    ]);

                    $bitacora['accion'] = bitacora::$acciones[12] ?? null;
                    $bitacora['tabla'] = 'BENEFICIARIOS_ACTIVIDADES';
                    break;

                default:
                    throw new Exception('Tipo no soportado', 400);
            }

            bitacora::create([
                'accion' => $bitacora['accion'],
                'tabla' => $bitacora['tabla'],
                'descripcion' => 'SE INSCRIBIO EXTRANJERO ID : ' . $inscripcion->id,
                'created_at' => now(),
                'usuario_id' => auth()->user()->id ?? null,
                'beneficiario_id' => $request->extranjero_id,
                'identificador' => $inscripcion->id,
            ]);

            DB::connection('gds')->commit();

            return response()->json(['message' => 'Extranjero asignado correctamente'], 201);
        } catch (\Throwable $th) {
            DB::connection('gds')->rollBack();

            $status = method_exists($th, 'getCode') && intval($th->getCode()) >= 100 && intval($th->getCode()) < 600
                ? intval($th->getCode())
                : 500;

            return response()->json([
                'error' => 'Error al inscribir al extranjero',
                'message' => $th->getMessage()
            ], $status);
        }
    }

    public function buscarRecurso(string $tipo, int $codigo) {
        try {
            $recurso = null;

            switch ($tipo) {
                case 'modulo':
                    $recurso = modulos::with([
                        'tarifas',
                        'programa.escuela',
                        'sede'
                    ])->where('id', $codigo)->first();
                    break;

                case 'curso':
                    $recurso = detalles_cursos::with([
                        'curso',
                        'tarifas',
                        'programa.escuela',
                        'sede'
                    ])->where('id', $codigo)->first();
                    break;

                case 'actividad':
                    $recurso = detalles_actividades::with([
                        'actividad',
                        'tarifas',
                        'programa.escuela',
                        'sede'
                    ])->where('id', $codigo)->first();
                    break;

                default:
                    $recurso = null;
            }

            return $recurso;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }

    /**
     * Crear partidas en SAP — robustecidas con checks de nulos
     *
     * @param array $interlocutor
     * @param modulos|detalles_cursos|detalles_actividades|null $recurso
     * @param int $extranjero_id
     * @param string $tipo
     * @return bool
     */
    public function crearPartidasSap(array $interlocutor, $recurso, int $extranjero_id, string $tipo, string $anio_mes) {
        try {
            $ic = null;

            if (!empty($interlocutor['IC_ENCARGADO'] ?? null) && trim($interlocutor['IC_ENCARGADO']) !== '') {
                $ic = trim($interlocutor['IC_ENCARGADO']);
            } elseif (!empty($interlocutor['IC_ALUMNO'] ?? null)) {
                $ic = trim($interlocutor['IC_ALUMNO']);
            }

            $nombre_recurso = null;
            
            switch ($tipo) {
                case 'modulo':
                    $nombre_recurso = $recurso->nombre ?? null;
                    break;
                case 'curso':
                    $nombre_recurso = $recurso->curso->nombre ?? null;
                    break;
                case 'actividad':
                    $nombre_recurso = $recurso->actividad->nombre ?? null;
                    break;
            }

            if (!$ic) {
                // no hay interlocutor, no creamos partidas
                return false;
            }

            $extranjero = $this->buscarExtranjero($extranjero_id);

            if (!$extranjero) {
                throw new Exception('Extranjero no encontrado', 404);
            }

            // actualizamos interlocutor en beneficiario
            $extranjero->update(['interlocutor' => $ic]);

            // validar tarifas antes de usar
            $tarifas = $recurso->tarifas ?? null;
            if ($tarifas && floatval($tarifas->inscripcion) > 0) {
                $params = [
                    'INTERLOCUTOR' => $ic,
                    'OP_PRINCIPAL' => optional($recurso->sede)->sede_op_principal ?? optional($recurso->programa->escuela)->op_principal,
                    'OP_PARCIAL' => optional($recurso->sede)->sede_op_parcial ?? optional($recurso->programa->escuela)->op_parcial,
                    'OBJETO_CONTRATO' => optional($recurso->sede)->sede_oc ?? optional($recurso->programa->escuela)->objeto_contrato,
                    'VALOR' => strval(floatval($tarifas->inscripcion)),
                    'PERIODO' => date('my', strtotime($this->sumMonth($anio_mes, 0))),
                    'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($anio_mes, 0)),
                    'DESCRIPCION' => 'INSCRIPCION ' . mb_strtoupper($nombre_recurso ?? ''),
                    'LLAVE_RECONCILIACION' => date('ymd')
                ];
                SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);
            }

            // pago cuota: decidir tarifa según edad del beneficiario (extranjero)
            $edad = intval($extranjero->edad ?? 999);
            $valorTarifa = $edad >= 18 ? ($tarifas->tarifa_mayor ?? 0) : ($tarifas->tarifa_menor ?? 0);

            $params = [
                'INTERLOCUTOR' => $ic,
                'OP_PRINCIPAL' => optional($recurso->sede)->sede_op_principal ?? optional($recurso->programa->escuela)->op_principal,
                'OP_PARCIAL' => optional($recurso->sede)->sede_op_parcial ?? optional($recurso->programa->escuela)->op_parcial,
                'OBJETO_CONTRATO' => optional($recurso->sede)->sede_oc ?? optional($recurso->programa->escuela)->objeto_contrato,
                'VALOR' => strval(floatval($valorTarifa)),
                'PERIODO' => date('my', strtotime($this->sumMonth($anio_mes ?? date('Y-m'), 0))),
                'FECHA_VENCIMIENTO' => $this->ultimoDiaFormatoYmd($this->sumMonth($anio_mes ?? date('Y-m'), 0)),
                'DESCRIPCION' => 'PAGO CUOTA ' . mb_strtoupper($nombre_recurso ?? ''),
                'LLAVE_RECONCILIACION' => date('ymd')
            ];

            SAP::rfc_name('Z_ZFUN_PSCD_00003_005')->params($params);

            return true;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }

    public function consultaCreacionInterlocutorSap($recurso, int $extranjero_id) {
        try {
            $extranjero = $this->buscarExtranjero($extranjero_id);

            if (!$extranjero) {
                throw new Exception('Extranjero no encontrado', 404);
            }

            $params = [
                'TIPO_ESCUELA' => (isset($recurso->programa->dependencia_id) && (int)$recurso->programa->dependencia_id == 8) ? '1' : '2',
                'ALUMNO' => strval($extranjero->codigo_alumno ?? ''),
                'DPI' => $extranjero->cui ?? '',
                'PRIMER_NOMBRE' => $extranjero->primer_nombre ?? '',
                'SEGUNDO_NOMBRE' => $extranjero->segundo_nombre ?? '.',
                'PRIMER_APELLIDO' => $extranjero->primer_apellido ?? '',
                'SEGUNDO_APELLIDO' => $extranjero->segundo_apellido ?? '.',
                'FECHA_NACIMIENTO' => isset($extranjero->fecha_nacimiento) ? date('Ymd', strtotime($extranjero->fecha_nacimiento)) : null,
                'DIRECCION' => optional($extranjero->domicilio)->direccion,
                'ZONA' => optional($extranjero->domicilio) ? 'ZONA ' . optional($extranjero->domicilio)->zona_id : null,
            ];

            if (intval($extranjero->edad ?? 999) < 18) {
                $params += [
                    'PNOMBRE_ENCARGADO' => $extranjero->responsable->primer_nombre ?? '',
                    'SNOMBRE_ENCARGADO' => $extranjero->responsable->segundo_nombre ?? '.',
                    'PAPELLIDO_ENCARGADO' => $extranjero->responsable->primer_apellido ?? '',
                    'SAPELLIDO_ENCARGADO' => $extranjero->responsable->segundo_apellido ?? '.',
                    'FNACIMIENTO_ENCARGADO' => isset($extranjero->responsable->fecha_nacimiento) ? date('Ymd', strtotime($extranjero->responsable->fecha_nacimiento)) : null,
                    'DPI_ENCARGADO' => $extranjero->responsable->cui ?? ''
                ];
            }

            $interlocutor = SAP::rfc_name('Z_ZFUN_PSCD_00003_004')->params($params);

            return $interlocutor;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }

    public function verificarUnicoInscrito(int $year, string $tipo, int $codigo, int $extranjero_id): bool {
        try {
            switch ($tipo) {
                case 'modulo':
                    return beneficiarios_modulos::where('anio_inscripcion', $year)
                        ->where('modulo_id', $codigo)
                        ->where('beneficiario_id', $extranjero_id)
                        ->exists();
                case 'curso':
                    return beneficiarios_cursos::where('anio_inscripcion', $year)
                        ->where('detalle_curso_id', $codigo)
                        ->where('beneficiario_id', $extranjero_id)
                        ->exists();
                case 'actividad':
                    // aquí usamos whereYear para created_at
                    return beneficiarios_actividades::whereYear('created_at', $year)
                        ->where('detalle_actividad_id', $codigo)
                        ->where('beneficiario_id', $extranjero_id)
                        ->exists();
                default:
                    return false;
            }
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }

    public function buscarExtranjero(int $extranjero_id) {
        try {
            return beneficiarios::with(['domicilio', 'responsable'])->where('id', $extranjero_id)->first();
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }

    public function sumMonth(string $fecha, int $mesesASumar): string {
        $timestamp = strtotime($fecha . "-01");
        $nuevaFecha = date("Y-m", strtotime("+$mesesASumar month", $timestamp));
        return $nuevaFecha;
    }

    public function ultimoDiaFormatoYmd(string $fechaYm): string {
        $timestamp = strtotime($fechaYm . "-01");
        $ultimoDia = date("t", $timestamp);
        return date("Ym", $timestamp) . $ultimoDia;
    }
}
