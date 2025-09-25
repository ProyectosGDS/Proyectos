<?php

namespace App\Http\Controllers\Programas;

use App\Http\Controllers\Controller;
use App\Models\adm_gds\beneficiarios_cursos;
use App\Models\adm_gds\beneficiarios_modulos;
use App\Models\adm_gds\control_asistencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlAsistenciaController extends Controller
{
    public function getBeneficiariosCurso(Request $request) {
        
        $request->validate([
            'detalle_curso_id' => 'required|integer',
            'year' => 'required|integer|date_format:Y',
            'fecha' => 'required|date|date_format:Y-m-d',
        ]);

        try {

            $beneficiarios_inscritos = beneficiarios_cursos::has('beneficiario')
                ->with([
                    'beneficiario' => function($query) {
                        $query->where('estado','V')->orderBy('primer_apellido');
                    },
                    'beneficiario.control_asistencia' => function($query) use ($request) {
                        $query->where('tipo','curso')
                            ->where('curso_modulo_id',$request->detalle_curso_id)
                            ->where('fecha',$request->fecha);
                    }
                ])->where('detalle_curso_id',$request->detalle_curso_id)
                ->where('estado','A')
                ->whereYear('created_at',$request->year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function getBeneficiariosModulo(Request $request) {
        
        $request->validate([
            'modulo_id' => 'required|integer',
            'year' => 'required|integer|date_format:Y',
            'fecha' => 'required|date|date_format:Y-m-d',
        ]);

        try {

            $beneficiarios_inscritos = beneficiarios_modulos::has('beneficiario')
                ->with([
                    'beneficiario' => function($query) {
                        $query->where('estado','V')->orderBy('primer_apellido');
                    },
                    'beneficiario.control_asistencia' => function($query) use ($request) {
                        $query->where('tipo','modulo')
                            ->where('curso_modulo_id',$request->modulo_id)
                            ->where('fecha',$request->fecha);
                    }
                ])->where('modulo_id',$request->modulo_id)
                ->where('estado','A')
                ->whereYear('created_at',$request->year)
                ->get();

            return response($beneficiarios_inscritos);
        } catch (\Throwable $th) {
            return response($th->getMessage());
        }
    }

    public function registrarAsistencias(Request $request, int $curso_modulo_id) {
        $validated = $request->validate([
            'asistencia' => 'nullable|array',
            'tipo' => 'required|string|in:curso,modulo',
            'fecha' => 'required|date|date_format:Y-m-d',
        ]);

        
        try {
            
            DB::connection('gds')->beginTransaction();

            $beneficiariosConAsistencia = collect($validated['asistencia']);

            $asistenciasExistentes = control_asistencia::where([
                'curso_modulo_id' => $curso_modulo_id,
                'fecha' => $validated['fecha'],
                'tipo' => $validated['tipo'],
            ])->get();

            $idsExistentes = $asistenciasExistentes->pluck('beneficiario_id');

            $beneficiariosAEliminar = $idsExistentes->diff($beneficiariosConAsistencia);

            if ($beneficiariosAEliminar->isNotEmpty()) {
                control_asistencia::where([
                    'curso_modulo_id' => $curso_modulo_id,
                    'fecha' => $validated['fecha'],
                    'tipo' => $validated['tipo'],
                ])->whereIn('beneficiario_id', $beneficiariosAEliminar)->delete();
            }

            $nuevosBeneficiarios = $beneficiariosConAsistencia->diff($idsExistentes);

            if ($nuevosBeneficiarios->isNotEmpty()) {
                $nuevasAsistencias = $nuevosBeneficiarios->map(function ($beneficiarioId) use ($curso_modulo_id, $validated) {
                    return [
                        'beneficiario_id' => $beneficiarioId,
                        'curso_modulo_id' => $curso_modulo_id,
                        'fecha' => $validated['fecha'],
                        'tipo' => $validated['tipo'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                control_asistencia::insert($nuevasAsistencias);
            }

            DB::connection('gds')->commit();

            return response('Asistencia registrada correctamente', 200);
        } catch (\Throwable $th) {
            DB::connection('gds')->rollBack();
            return response('Error al registrar asistencia: ' . $th->getMessage(), 500);
        }
    }

    public function listadoAsistencia(Request $request) {
        $request->validate([
            'detalle_curso_id' => 'required|integer',
            'year' => 'required|integer|date_format:Y',
            'fecha' => 'required|date|date_format:Y-m-d',
            'tipo' => 'required',
        ]);

        try {

            if($request->tipo == 'curso') {

                $beneficiarios_inscritos = beneficiarios_cursos::has('beneficiario')
                    ->with([
                        'beneficiario' => function($query) {
                            $query->where('estado','V')->orderBy('primer_apellido');
                        }
                    ])->where('detalle_curso_id',$request->detalle_curso_id)
                    ->where('estado','A')
                    ->whereYear('created_at',$request->year)
                    ->get();
                
                $fecha = $request->fecha;
                $curso = $beneficiarios_inscritos->first()->curso->load(['curso','modulo','programa.dependencia','sede','horarios','instructor','temporalidad']) ?? null;
                $pdf = Pdf::loadView('Reports.PdfControlAsistenciaCurso',compact('beneficiarios_inscritos','fecha','curso'));
                
            } else if($request->tipo == 'modulo') {

                $beneficiarios_inscritos = beneficiarios_modulos::has('beneficiario')
                    ->with([
                        'beneficiario' => function($query) {
                            $query->where('estado','V')->orderBy('primer_apellido');
                        }
                    ])->where('modulo_id',$request->detalle_curso_id)
                    ->where('estado','A')
                    ->whereYear('created_at',$request->year)
                    ->get();
                
                $fecha = $request->fecha;
                $modulo = $beneficiarios_inscritos->first()->modulo->load(['programa.dependencia','sede']) ?? null;
                $pdf = Pdf::loadView('Reports.PdfControlAsistenciaModulo',compact('beneficiarios_inscritos','fecha','modulo'));
            }


            
            // return $pdf->stream('control-asistencia.pdf');

            // Descargar el PDF con un nombre específico
            return $pdf->download('control-asistencia.pdf');

            // return view('Reports.PdfControlAsistencia',compact('beneficiarios_inscritos','fecha','curso'));

        } catch (\Throwable $th) {
            return response($th->getMessage());
            
        }
        
    }
}
