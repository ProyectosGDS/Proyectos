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
use App\Rules\ValidateCui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrasladosController extends Controller
{

    public function busquedaBeneficiarios(Request $request) {
        $request->validate([
            'cui' => ['required','digits:13', new ValidateCui ,'exists:beneficiarios,cui'],
            'anio_inscripcion' => 'required|in:'.(date('Y') - 1).','.date('Y').','. (date('Y') + 1),
        ]);

        try {

            $beneficiario = beneficiarios::where('cui', $request->cui)
                ->where('estado', 'V')
                ->first();
            
            if(!$beneficiario){
                
                return response([
                    'code' => '2',
                    'message' => 'Beneficiario no encontrado o con estado no válido',
                    'beneficiario' => null,
                ]);
            }

            return response([
                'code' => '1',
                'message' => 'Beneficiario encontrado exitosamente',
                'beneficiario' => $beneficiario,
            ]);

        } catch (\Throwable $th) {
            return response([
                'code' => 'error',
                'message' => 'Error al buscar beneficiario: ' . $th->getMessage(),
                'beneficiario' => null,
            ],$th->getCode() ?: 500);
        }
    }

    public function validarRecursoActual(Request $request) {
        $request->validate([
            'cui' => ['required','digits:13', new ValidateCui ,'exists:beneficiarios,cui'],
            'anio_inscripcion' => 'required|in:'.(date('Y') - 1).','.date('Y').','. (date('Y') + 1),
            'beneficiario_id' => 'required|integer|exists:beneficiarios,id',
            'tipo_recurso_actual' => 'required|in:curso,modulo,actividad',
            'recurso_id_actual' => [
                'required',
                'integer', 
                function ($attribute, $value, $fail) use($request) {
                    $recurso = match($request->tipo_recurso_actual) {
                        'curso' => detalles_cursos::where('id',$value)->where('estado','A'),
                        'modulo' => modulos::where('id',$value)->where('estado','A'),
                        'actividad' => detalles_actividades::where('id',$value)->where('estado_actividad_id',1),
                    };

                    if(!$recurso->exists()) {
                        $fail("La asignación actual seleccionada no existe o esta inactivo");
                    }
                },
            ]
        ]);

        try {

            $validarRecurso = $this->validarRecursoActualAsignadoBeneficiario(
                $request->beneficiario_id,
                $request->recurso_id_actual, 
                $request->tipo_recurso_actual,
                $request->anio_inscripcion
            );

            if($validarRecurso) {
                return response([
                    'code' => '1',
                    'message' => 'Recurso valido',
                    'recurso' => $validarRecurso,
                ]);
            }

            return response([
                'code' => '2',
                'message' => 'Recurso no valido',
                'recurso' => null
            ]);
            
        } catch (\Throwable $th) {
            return response([
                'code' => 'error',
                'message' => 'Error al validar recurso: ' . $th->getMessage(),
                'beneficiario' => null,
            ],$th->getCode() ?: 500);
        }
    }

    public function validarRecursoActualAsignadoBeneficiario($beneficiario_id, $id, $tipo, $anio) {

        $recurso = match($tipo) {
            'curso' => detalles_cursos::with('curso')->where('id',$id),
            'modulo' => modulos::where('id',$id),
            'actividad' => detalles_actividades::with('actividad')->where('id',$id),
        };

        if(!$recurso->first()) {
            throw new \Exception('El recurso actual no existe');
        }

        $inscripcion = match($tipo) {
            'curso' => beneficiarios_cursos::where('beneficiario_id',$beneficiario_id)->where('detalle_curso_id',$id),
            'modulo' => beneficiarios_modulos::where('beneficiario_id',$beneficiario_id)->where('modulo_id',$id),
            'actividad' => beneficiarios_actividades::where('beneficiario_id',$beneficiario_id)->where('detalle_actividad_id',$id),
        };

        if(!$inscripcion->where('estado','A')->where('anio_inscripcion',$anio)->exists()) {
            throw new \Exception('El recurso actual no esta asignado al beneficiario o la inscripcion esta inactiva');
        }

        return $recurso->first();
    }

    public function validarRecursoNuevo(Request $request) {
        $request->validate([
            'cui' => ['required','digits:13', new ValidateCui ,'exists:beneficiarios,cui'],
            'anio_inscripcion' => 'required|in:'.(date('Y') - 1).','.date('Y').','. (date('Y') + 1),
            'tipo_recurso_actual' => 'required|in:curso,modulo,actividad',
            'recurso_id_actual' => [
                'required',
                'integer', 
                function ($attribute, $value, $fail) use($request) {
                    $recurso = match($request->tipo_recurso_actual) {
                        'curso' => detalles_cursos::where('id',$value)->where('estado','A'),
                        'modulo' => modulos::where('id',$value)->where('estado','A'),
                        'actividad' => detalles_actividades::where('id',$value)->where('estado_actividad_id',1),
                    };

                    if(!$recurso->exists()) {
                        $fail("La asignación actual seleccionada no existe o esta inactivo");
                    }
                },
            ],
            'tipo_recurso_nuevo' => 'required|in:curso,modulo,actividad',
            'recurso_id_nuevo' => [
                'required',
                'integer', 
                function ($attribute, $value, $fail) use($request) {
                    $recurso = match($request->tipo_recurso_nuevo) {
                        'curso' => detalles_cursos::where('id',$value)->where('estado','A')->where('capacidad','>=',1),
                        'modulo' => modulos::where('id',$value)->where('estado','A')->where('capacidad','>=',1),
                        'actividad' => detalles_actividades::where('id',$value)->where('estado_actividad_id',1),
                    };

                    if(!$recurso->exists()) {
                        $fail("La asignación nueva seleccionada no existe o esta inactiva o no tiene capacidad");
                    }
                },
            ],
        ]);

        try {
            $recurso = $this->validarRecursoNuevoAsignadoBeneficiario(
                $request->beneficiario_id,
                $request->recurso_id_nuevo,
                $request->tipo_recurso_nuevo,
                $request->anio_inscripcion
            );

            if($recurso) {
                return response([
                    'code' => '1',
                    'message' => 'Recurso valido',
                    'recurso' => $recurso,
                ]);
            }

            return response([
                'code' => '2',
                'message' => 'Recurso no valido',
                'recurso' => null
            ]);

        } catch (\Throwable $th) {
            return response([
                'code' => 'error',
                'message' => 'Error al validar recurso: ' . $th->getMessage(),
                'beneficiario' => null,
            ],$th->getCode() ?: 500);
        }
    }

    public function validarRecursoNuevoAsignadoBeneficiario($beneficiario_id, $id, $tipo, $anio) {

        $recurso = match($tipo) {
            'curso' => detalles_cursos::with('curso')->where('id',$id),
            'modulo' => modulos::where('id',$id),
            'actividad' => detalles_actividades::with('actividad')->where('id',$id),
        };

        if(!$recurso->first()) {
            throw new \Exception('El recurso nuevo no existe');
        }

        $inscripcion = match($tipo) {
            'curso' => beneficiarios_cursos::where('beneficiario_id',$beneficiario_id)->where('detalle_curso_id',$id),
            'modulo' => beneficiarios_modulos::where('beneficiario_id',$beneficiario_id)->where('modulo_id',$id),
            'actividad' => beneficiarios_actividades::where('beneficiario_id',$beneficiario_id)->where('detalle_actividad_id',$id),
        };

        if($inscripcion->where('anio_inscripcion',$anio)->exists()) {
            throw new \Exception('El beneficiario ya esta asignado en este recurso');
        }

        return $recurso->first();
    }

    public function realizarTraslado(Request $request) {
        $request->validate([
            'cui' => ['required','digits:13', new ValidateCui ,'exists:beneficiarios,cui'],
            'anio_inscripcion' => 'required|in:'.(date('Y') - 1).','.date('Y').','. (date('Y') + 1),
            'beneficiario_id' => 'required|integer|exists:beneficiarios,id',
            'tipo_recurso_actual' => 'required|in:curso,modulo,actividad',
            'recurso_id_actual' => [
                'required',
                'integer', 
                function ($attribute, $value, $fail) use($request) {
                    $recurso = match($request->tipo_recurso_actual) {
                        'curso' => detalles_cursos::where('id',$value)->where('estado','A'),
                        'modulo' => modulos::where('id',$value)->where('estado','A'),
                        'actividad' => detalles_actividades::where('id',$value)->where('estado_actividad_id',1),
                    };

                    if(!$recurso->exists()) {
                        $fail("La asignación actual seleccionada no existe o esta inactivo");
                    }
                },
            ],
            'tipo_recurso_nuevo' => 'required|in:curso,modulo,actividad',
            'recurso_id_nuevo' => [
                'required',
                'integer', 
                function ($attribute, $value, $fail) use($request) {
                    $recurso = match($request->tipo_recurso_nuevo) {
                        'curso' => detalles_cursos::where('id',$value)->where('estado','A')->where('capacidad','>=',1),
                        'modulo' => modulos::where('id',$value)->where('estado','A')->where('capacidad','>=',1),
                        'actividad' => detalles_actividades::where('id',$value)->where('estado_actividad_id',1),
                    };

                    if(!$recurso->exists()) {
                        $fail("La asignación nueva seleccionada no existe o esta inactiva o no tiene capacidad");
                    }
                },
            ],
        ]);

        try {

            DB::beginTransaction();

                $inscripcion = $this->inscribirBeneficiarioNuevoRecurso(
                    $request->beneficiario_id,
                    $request->tipo_recurso_nuevo,
                    $request->recurso_id_nuevo,
                    $request->anio_inscripcion
                );

                $this->registrarInscripcionNuevaBitacoralBeneficiario(
                    $request->tipo_recurso_nuevo,
                    $inscripcion->id,
                    $request->beneficiario_id
                );

                $inscripcion_actual = $this->desactivarInscripcionBeneficiarioActual(
                    $request->beneficiario_id,
                    $request->recurso_id_actual,
                    $request->tipo_recurso_actual,
                    $request->anio_inscripcion
                );

                $this->registrarInactivacionInscripcionBitacoralBeneficiario(
                    $request->tipo_recurso_actual,
                    $inscripcion_actual->id,
                    $request->beneficiario_id
                );

            DB::commit();

            return response([
                'message' => 'Se realizo el traslado de forma exitosa.'
            ]);


        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'code' => 'error',
                'message' => 'Error al realizar traslado: ' . $th->getMessage(),
            ],$th->getCode() ?: 500);
        }
    }

    public function inscribirBeneficiarioNuevoRecurso($beneficiario_id, $tipo, $id, $anio) {
        $datos = [
            'beneficiario_id' => $beneficiario_id,
            'anio_inscripcion' => $anio,
            'estado' => 'A',
            'created_at' => now()
        ];

        return match($tipo) {
            'curso' => beneficiarios_cursos::create(array_merge($datos, ['detalle_curso_id' => $id])),
            'modulo' => beneficiarios_modulos::create(array_merge($datos, ['modulo_id' => $id])),
            'actividad' => beneficiarios_actividades::create(array_merge($datos, ['detalle_actividad_id' => $id])),
            default => throw new \Exception("Error al crear la nueva inscripción: tipo no soportado")
        };
    }

    public function desactivarInscripcionBeneficiarioActual($beneficiario_id, $id, $tipo, $anio) {
        // Obtenemos el registro primero para asegurar que existe y obtener su ID para la bitácora
        $inscripcion = match($tipo) {
            'curso' => beneficiarios_cursos::where('detalle_curso_id',$id),
            'modulo' => beneficiarios_modulos::where('modulo_id',$id),
            'actividad' => beneficiarios_actividades::where('detalle_actividad_id',$id),
        };

        $registro = $inscripcion->where('beneficiario_id',$beneficiario_id)->where('anio_inscripcion', $anio)->first();

        if (!$registro) {
            throw new \Exception("No se encontró el registro actual para desactivar.");
        }

        $registro->update(['estado' => 'I']);
        return $registro;
    }

    public function registrarInscripcionNuevaBitacoralBeneficiario($tipo, $inscripcion_id, $beneficiario_id) {
        $accion = match($tipo) {
            'curso' => bitacora::$acciones[4] ?? 'INSCRIPCION_CURSO',
            'modulo' => bitacora::$acciones[8] ?? 'INSCRIPCION_MODULO',
            'actividad' => bitacora::$acciones[12] ?? 'INSCRIPCION_ACTIVIDAD',
        };

        bitacora::create([
            'accion' => $accion,
            'tabla' => "BENEFICIARIOS_" . strtoupper($tipo) . "S",
            'descripcion' => "SE INSCRIBIO BENEFICIARIO. ID REGISTRO: $inscripcion_id",
            'usuario_id' => auth()->id(),
            'beneficiario_id' => $beneficiario_id,
            'identificador' => $inscripcion_id,
            'created_at' => now(),
        ]);
    }

    public function registrarInactivacionInscripcionBitacoralBeneficiario($tipo, $inscripcion_id, $beneficiario_id) {
        $accion = match($tipo) {
            'curso' => bitacora::$acciones[6] ?? 'DESACTIVACION_CURSO',
            'modulo' => bitacora::$acciones[10] ?? 'DESACTIVACION_MODULO',
            'actividad' => bitacora::$acciones[14] ?? 'DESACTIVACION_ACTIVIDAD',
        };

        bitacora::create([
            'accion' => $accion,
            'tabla' => "BENEFICIARIOS_" . strtoupper($tipo) . "S",
            'descripcion' => "TRASLADO: SE DESACTIVO POR TRASLADO. ID REGISTRO: $inscripcion_id",
            'usuario_id' => auth()->id(),
            'beneficiario_id' => $beneficiario_id,
            'identificador' => $inscripcion_id,
            'created_at' => now()
        ]);
    }
}